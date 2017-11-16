<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security;

use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Idna;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * UserChecker checks the user account flags.
 */
class UserChecker implements UserCheckerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var Session */
    protected $session;

    /** @var ScopeMatcher */
    protected $scopeMatcher;

    /** @var RequestStack */
    protected $requestStack;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator, \Swift_Mailer $mailer, Session $session, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->scopeMatcher = $scopeMatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->checkLoginAttempts($user);
        $this->checkIfAccountIsLocked($user);
        $this->checkIfAccountIsDisabled($user);
        $this->checkIfLoginIsAllowed($user);
        $this->checkIfAccountIsActive($user);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }

    /**
     * Lock the account if there are too many login attempts.
     *
     * @param User $user
     */
    protected function checkLoginAttempts(User $user): void
    {
        if ($user->loginCount < 1) {
            $time = time();
            $user->locked = $time;
            $user->loginCount = (int) Config::get('loginCount');
            $user->save();

            $lockMinutes = ceil((int) Config::get('lockPeriod') / 60);

            $this->setAccountLockedFlashBag($user);
            $this->logger->info(
                sprintf('User %s has been locked for %s minutes', $user->getUsername(), $lockMinutes),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            // Send admin notification
            if (Config::get('adminEmail')) {
                $request = $this->requestStack->getCurrentRequest();

                if ($request && $this->scopeMatcher->isFrontendRequest($request)) {
                    $realName = sprintf('%s %s', $user->firstname, $user->lastname);
                } else {
                    $realName = $user->name;
                }

                $website = Idna::decode(Environment::get('base'));
                $lockMinutes = ceil(((int) Config::get('lockPeriod')) / 60);

                $subject = $this->translator->trans('MSC.lockedAccount.0', [], 'contao_default');
                $body = $this->translator->trans(
                    'MSC.lockedAccount.1',
                    [
                        $user->getUsername(),
                        $realName,
                        $website,
                        $lockMinutes,
                    ],
                    'contao_default'
                );

                $email = new \Swift_Message();
                $email
                    ->setTo(Config::get('adminEmail'))
                    ->setSubject($subject)
                    ->setBody($body, 'text/plain')
                ;

                $this->mailer->send($email);
            }

            throw new LockedException();
        }
    }

    /**
     * Check whether the account is locked.
     *
     * @param User $user
     */
    protected function checkIfAccountIsLocked(User $user): void
    {
        if (false === $user->isAccountNonLocked()) {
            $this->setAccountLockedFlashBag($user);

            throw new LockedException();
        }
    }

    /**
     * Check whether the account is disabled.
     *
     * @param User $user
     */
    protected function checkIfAccountIsDisabled(User $user): void
    {
        if (false === $user->isEnabled()) {
            $this->setInvalidLoginFlashBag();
            $this->logger->info(
                'The account has been disabled',
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            throw new DisabledException();
        }
    }

    /**
     * Check wether login is allowed (front end only).
     *
     * @param User $user
     */
    protected function checkIfLoginIsAllowed(User $user): void
    {
        if ($user instanceof FrontendUser && false === $user->login) {
            $this->setInvalidLoginFlashBag();
            $this->logger->info(
                vsprintf(
                    'User %s is not allowed to log in',
                    [$user->getUsername()]
                ),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            throw new DisabledException();
        }
    }

    /**
     * Check whether account is not active yet or anymore.
     *
     * @param User $user
     */
    protected function checkIfAccountIsActive(User $user): void
    {
        $start = (int) $user->start;
        $stop = (int) $user->stop;
        $time = Date::floorToMinute(time());
        $notActiveYet = $start && $start > $time;
        $wasNotActive = $stop && $stop <= ($time + 60);
        $logMessage = '';

        if ($notActiveYet) {
            $logMessage = sprintf(
                'The account was not active yet (activation date: %s)',
                Date::parse(Config::get('dateFormat'), $start)
            );
        }

        if ($wasNotActive) {
            $logMessage = sprintf(
                'The account was not active anymore (deactivation date: %s)',
                Date::parse(Config::get('dateFormat'), $stop)
            );
        }

        if ($notActiveYet || $wasNotActive) {
            $this->setInvalidLoginFlashBag();
            $this->logger->info($logMessage, ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]);

            throw new DisabledException();
        }
    }

    /**
     * Set Session Flash Bag with invalidLogin error message.
     */
    protected function setInvalidLoginFlashBag(): void
    {
        $this->session->getFlashBag()->set(
            $this->getFlashType(),
            $this->translator->trans(
                'ERR.invalidLogin',
                [],
                'contao_default'
            )
        );
    }

    /**
     * Set Session Flash Bag with accountLocked error message.
     *
     * @param User $user
     */
    protected function setAccountLockedFlashBag(User $user): void
    {
        $this->session->getFlashBag()->set(
            $this->getFlashType(),
            $this->translator->trans(
                'ERR.accountLocked',
                [ceil((($user->locked + (int) Config::get('lockPeriod')) - time()) / 60)],
                'contao_default'
            )
        );
    }

    /**
     * Gets flash type from providerKey.
     *
     * @return string
     */
    private function getFlashType(): string
    {
        $type = '';

        $request = $this->requestStack->getCurrentRequest();

        if ($request && $this->scopeMatcher->isFrontendRequest($request)) {
            $type = 'contao.FE.error';
        }

        if ($request && $this->scopeMatcher->isBackendRequest($request)) {
            $type = 'contao.BE.error';
        }

        return $type;
    }
}
