<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class for handling the authentication for the Contao frontend preview.
 */
class FrontendPreviewAuthenticator
{
    protected $requestStack;
    protected $session;
    protected $tokenStorage;
    protected $userProvider;
    protected $logger;

    /**
     * Constructor.
     *
     * @param RequestStack          $requestStack
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param UserProviderInterface $userProvider
     */
    public function __construct(RequestStack $requestStack, SessionInterface $session, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider)
    {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
    }

    /**
     * Authenticate a frontend user based on the username.
     *
     * @param null $username
     */
    public function authenticateFrontendUser($username = null): void
    {
        $sessionKey = '_security_contao_frontend';
        $providerKey = 'contao_frontend';
        $request = $this->requestStack->getCurrentRequest();

        if (null === $username) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            // TODO:
        }

        $token = new UsernamePasswordToken(
            $user,
            null,
            $providerKey,
            $user->getRoles()
        );

        if (null === $token) {
            if ($request->hasPreviousSession()) {
                $this->session->remove($sessionKey);
            }
        } else {
            $this->session->set($sessionKey, serialize($token));
        }
    }
}
