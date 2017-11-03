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

use Contao\System;
use Contao\User;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Legacy AuthenticationProvider to maintain the checkCredentials hook.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 */
class LegacyContaoAuthenticationProvider extends DaoAuthenticationProvider
{
    /**
     * The checkCredentials hook is triggered when a login attempt fails due to a wrong password.
     * It passes the username and password as well as the user object as arguments
     * and expects a boolean as return value.
     *
     * @param UserInterface $user
     * @param UsernamePasswordToken $token
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    public function checkAuthentication(UserInterface $user, UsernamePasswordToken $token): void
    {
        try {
            parent::checkAuthentication($user, $token);
        } catch(BadCredentialsException $badCredentialsException) {
            if (!$user instanceof User) {
                throw $badCredentialsException;
            }

            $authenticated = false;

            // HOOK: pass credentials to callback functions
            if (isset($GLOBALS['TL_HOOKS']['checkCredentials']) && is_array($GLOBALS['TL_HOOKS']['checkCredentials'])) {
                foreach ($GLOBALS['TL_HOOKS']['checkCredentials'] as $callback) {
                    $user->objAuth = System::importStatic($callback[0], 'objAuth', true);
                    $authenticated = $user->objAuth->{$callback[1]}($token->getUsername(), $token->getCredentials(), $user);

                    // Authentication successfull
                    if (true === $authenticated) {
                        break;
                    }
                }
            }

            if (false === $authenticated) {
                throw $badCredentialsException;
            }
        }
    }
}
