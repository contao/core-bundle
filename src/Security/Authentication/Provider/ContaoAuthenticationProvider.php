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

use Contao\CoreBundle\Event\CheckCredentialsEvent;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * ContaoAuthenticationProvider extends the existing Symfony DaoAuthenticationProvider
 * to provide some Brute-Force protection against the login form.
 */
class ContaoAuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var Session */
    protected $session;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $providerKey;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions, LoggerInterface $logger, Session $session, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);

        $this->logger = $logger;
        $this->session = $session;
        $this->translator = $translator;
        $this->providerKey = $providerKey;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthentication(UserInterface $user, UsernamePasswordToken $token): void
    {
        try {
            parent::checkAuthentication($user, $token);
        } catch (BadCredentialsException $badCredentialsException) {
            if (!$user instanceof User) {
                throw $badCredentialsException;
            }

            /** @var CheckCredentialsEvent $event */
            $event = $this->eventDispatcher->dispatch(
                CheckCredentialsEvent::NAME,
                new CheckCredentialsEvent($token->getUsername(), $token->getCredentials(), $user)
            );

            if (false === $event->getVote() && false === $this->triggerLegacyCheckCredentialsHook($user, $token)) {
                --$user->loginCount;
                $user->save();

                $this->session->getFlashBag()->set(
                    $this->getFlashType(),
                    $this->translator->trans(
                        'ERR.invalidLogin',
                        [],
                        'contao_default'
                    )
                );

                $this->logger->info(
                    sprintf('Invalid password submitted for username %s', $user->getUsername()),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );

                throw $badCredentialsException;
            }
        }
    }

    /**
     * The checkCredentials hook is triggered when a login attempt fails due to a wrong password.
     * It passes the username and password as well as the user object as arguments and expects a boolean as return
     * value.
     *
     * @param User                  $user
     * @param UsernamePasswordToken $token
     *
     * @return bool
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     *             Use the contao.checkCredentials event instead.
     */
    protected function triggerLegacyCheckCredentialsHook(User $user, UsernamePasswordToken $token): bool
    {
        @trigger_error('Using the checkCredentials hook has been deprecated and will no longer work in Contao 5.0. Use the contao.checkCredentials event instead.', E_USER_DEPRECATED);

        if (isset($GLOBALS['TL_HOOKS']['checkCredentials']) && is_array($GLOBALS['TL_HOOKS']['checkCredentials'])) {
            foreach ($GLOBALS['TL_HOOKS']['checkCredentials'] as $callback) {
                $user->objAuth = System::importStatic($callback[0], 'objAuth', true);

                if ($user->objAuth->{$callback[1]}($token->getUsername(), $token->getCredentials(), $user)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gets flash type from providerKey.
     *
     * @return string
     */
    private function getFlashType(): string
    {
        if ('contao_frontend' === $this->providerKey) {
            return 'contao.FE.error';
        }

        return 'contao.BE.error';
    }
}
