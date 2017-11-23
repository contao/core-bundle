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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FrontendUser;
use Psr\Log\LoggerInterface;
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
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param RequestStack          $requestStack
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param UserProviderInterface $userProvider
     * @param LoggerInterface       $logger
     */
    public function __construct(RequestStack $requestStack, SessionInterface $session, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider, LoggerInterface $logger)
    {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
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

        // check if a backend user is authenticated
        if (null === $this->tokenStorage->getToken() || !$this->tokenStorage->getToken()->isAuthenticated()) {
            return;
        }

        if (null === $username) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        try {
            /** @var FrontendUser $user */
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $this->logger->info(
                sprintf('FrontendUser with Username %s could not be found. Frontend authentication aborted.', $username),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            return;
        }

        $token = new UsernamePasswordToken(
            $user,
            null,
            $providerKey,
            (array) $user->getRoles()
        );

        if (false === $token->isAuthenticated()) {
            if ($request->hasPreviousSession()) {
                $this->session->remove($sessionKey);
            }
        } else {
            $this->session->set($sessionKey, serialize($token));
        }
    }
}
