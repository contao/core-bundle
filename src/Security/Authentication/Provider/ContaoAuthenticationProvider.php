<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication\Provider;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\Exception\LockedException;
use Contao\FrontendUser;
use Contao\Idna;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ContaoAuthenticationProvider extends DaoAuthenticationProvider
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param UserProviderInterface    $userProvider
     * @param UserCheckerInterface     $userChecker
     * @param string                   $providerKey
     * @param EncoderFactoryInterface  $encoderFactory
     * @param bool                     $hideUserNotFoundExceptions
     * @param ContaoFrameworkInterface $framework
     * @param TranslatorInterface      $translator
     * @param RequestStack             $requestStack
     * @param \Swift_Mailer            $mailer
     * @param LoggerInterface|null     $logger
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions, ContaoFrameworkInterface $framework, TranslatorInterface $translator, RequestStack $requestStack, \Swift_Mailer $mailer, LoggerInterface $logger = null)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);

        $this->framework = $framework;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthentication(UserInterface $user, UsernamePasswordToken $token): void
    {
        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot handle non-Contao users.');
        }

        try {
            parent::checkAuthentication($user, $token);
        } catch (AuthenticationException $exception) {
            if ($exception instanceof BadCredentialsException) {
                if ($this->triggerCheckCredentialsHook($user, $token)) {
                    $this->onSuccess($user);
                    return;
                }

                $exception = $this->onBadCredentials($user, $exception);
            }

            $this->logAccess($exception->getMessage(), $user);

            throw $exception;
        }

        $this->onSuccess($user);
    }

    private function onSuccess(User $user)
    {
        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $user->lastLogin = $user->currentLogin;
        $user->currentLogin = time();
        $user->loginCount = $config->get('loginCount');
        $user->save();

        $this->logAccess(sprintf('User "%s" has logged in', $user->getUsername()), $user);

        $this->triggerPostLoginHook($user);
    }

    /**
     * Count the login attempts and lock the user if it reaches zero.
     *
     * @param User                    $user
     * @param AuthenticationException $exception
     *
     * @return AuthenticationException
     */
    public function onBadCredentials(User $user, AuthenticationException $exception): AuthenticationException
    {
        --$user->loginCount;

        if ($user->loginCount < 1) {
            /** @var Config $config */
            $config = $this->framework->getAdapter(Config::class);

            $user->locked = time();
            $user->loginCount = (int) $config->get('loginCount');
            $user->save();

            $lockedSeconds = $user->locked + (int) $config->get('lockPeriod') - time();
            $lockedMinutes = (int) ceil($lockedSeconds / 60);

            $this->sendLockedEmail($user, $lockedMinutes);

            $exception = new LockedException(
                $lockedSeconds,
                sprintf('User "%s" has been locked for %s minutes', $user->getUsername(), $lockedMinutes),
                0,
                $exception
            );
            $exception->setUser($user);

            return $exception;
        }

        return new BadCredentialsException(
            sprintf('Invalid password submitted for username "%s"', $user->getUsername()),
            $exception->getCode(),
            $exception
        );
    }

    /**
     * Sends an email to the administrator that the account has been locked.
     *
     * @param User $user
     * @param int  $lockedMinutes
     */
    private function sendLockedEmail(User $user, int $lockedMinutes): void
    {
        $this->framework->initialize();

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        // Send admin notification
        if ($adminEmail = $config->get('adminEmail')) {
            $request = $this->requestStack->getMasterRequest();

            if (null === $request) {
                throw new \RuntimeException('The request stack did not contain a request');
            }

            $realName = $user->name;

            if ($user instanceof FrontendUser) {
                $realName = sprintf('%s %s', $user->firstname, $user->lastname);
            }

            $website = Idna::decode($request->getSchemeAndHttpHost());
            $subject = $this->translator->trans('MSC.lockedAccount.0', [], 'contao_default');

            $body = $this->translator->trans(
                'MSC.lockedAccount.1',
                [$user->getUsername(), $realName, $website, $lockedMinutes],
                'contao_default'
            );

            $email = new \Swift_Message();

            $email
                ->setFrom($adminEmail)
                ->setTo($adminEmail)
                ->setSubject($subject)
                ->setBody($body, 'text/plain')
            ;

            $this->mailer->send($email);
        }
    }

    /**
     * Logs the access message to the Contao back end.
     *
     * @param string $message
     * @param User   $user
     */
    private function logAccess(string $message, User $user)
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->info(
            $message,
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, $user->getUsername())]
        );
    }

    /**
     * Triggers the checkCredentials hook.
     *
     * @param User                  $user
     * @param UsernamePasswordToken $token
     *
     * @return bool
     */
    private function triggerCheckCredentialsHook(User $user, UsernamePasswordToken $token): bool
    {
        $this->framework->initialize();

        if (empty($GLOBALS['TL_HOOKS']['checkCredentials']) || !\is_array($GLOBALS['TL_HOOKS']['checkCredentials'])) {
            return false;
        }

        @trigger_error('Using the checkCredentials hook has been deprecated and will no longer work in Contao 5.0. Use the contao.check_credentials event instead.', E_USER_DEPRECATED);

        foreach ($GLOBALS['TL_HOOKS']['checkCredentials'] as $callback) {
            $objectInstance = $this->framework->createInstance($callback[0]);

            if ($objectInstance->{$callback[1]}($token->getUsername(), $token->getCredentials(), $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Triggers the postLogin hook.
     *
     * @param User $user
     */
    private function triggerPostLoginHook(User $user): void
    {
        $this->framework->initialize();

        if (empty($GLOBALS['TL_HOOKS']['postLogin']) || !\is_array($GLOBALS['TL_HOOKS']['postLogin'])) {
            return;
        }

        @trigger_error('Using the "postLogin" hook has been deprecated and will no longer work in Contao 5.0. Use the security.interactive_login event instead.', E_USER_DEPRECATED);

        foreach ($GLOBALS['TL_HOOKS']['postLogin'] as $callback) {
            $this->framework->createInstance($callback[0])->{$callback[1]}($user);
        }
    }
}
