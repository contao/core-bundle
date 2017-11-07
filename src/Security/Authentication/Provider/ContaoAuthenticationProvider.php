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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Contao\User;
use Psr\Log\LoggerInterface;
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

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions, LoggerInterface $logger, Session $session, TranslatorInterface $translator)
    {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);

        $this->logger = $logger;
        $this->session = $session;
        $this->translator = $translator;
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

            if (false === $this->triggerLegacyCheckCredentialsHook($user, $token)) {
                $user->loginCount--;
                $user->save();

                $this->session->getFlashBag()->set(
                    'contao.BE.error',
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
     */
    protected function triggerLegacyCheckCredentialsHook(User $user, UsernamePasswordToken $token): bool
    {
        @trigger_error('Using the checkCredentials hook has been deprecated and will no longer work in Contao 5.0. Use a custom AuthenticationProvider instead.', E_USER_DEPRECATED);

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
}
