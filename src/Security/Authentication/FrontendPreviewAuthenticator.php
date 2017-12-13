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

use Contao\BackendUser;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FrontendUser;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendPreviewAuthenticator
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param UserProviderInterface $userProvider
     * @param LoggerInterface|null  $logger
     */
    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider, LoggerInterface $logger = null)
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    /**
     * Authenticates a front end user based on the username.
     *
     * @param string $username
     *
     * @return bool
     */
    public function authenticateFrontendUser(string $username): bool
    {
        if (!$this->session->isStarted()) {
            return false;
        }

        $token = $this->tokenStorage->getToken();

        // Check if a back end user is authenticated
        if (null === $token || !$token->isAuthenticated()) {
            return false;
        }

        $backendUser = $token->getUser();

        if (!$backendUser instanceof BackendUser) {
            return false;
        }

        // Back end user does not have permission to log in front end users
        if (!$backendUser->isAdmin && (!\is_array($backendUser->amg) && !empty($backendUser->amg))) {
            return false;
        }

        try {
            $frontendUser = $this->userProvider->loadUserByUsername($username);

            if (!$frontendUser instanceof FrontendUser) {
                throw new UsernameNotFoundException('User is not a front end user');
            }
        } catch (UsernameNotFoundException $e) {
            if (null !== $this->logger) {
                $this->logger->info(
                    sprintf('Could not find a front end user with the username "%s"', $username),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
                );
            }

            return false;
        }

        $allowedGroups = StringUtil::deserialize($backendUser->amg, true);
        $frontendGroups = StringUtil::deserialize($frontendUser->groups, true);

        // Back end user does not have permission to log in front end users with that group
        if (!$backendUser->isAdmin && !\count(array_intersect($frontendGroups, $allowedGroups))) {
            return false;
        }

        $token = new FrontendPreviewToken($frontendUser);

        $this->session->set(FrontendUser::SECURITY_SESSION_KEY, serialize($token));

        return true;
    }

    /**
     * Removes a front end user authentication from the session.
     *
     * @return bool
     */
    public function removeFrontendUser(): bool
    {
        if (!$this->session->isStarted() || !$this->session->has(FrontendUser::SECURITY_SESSION_KEY)) {
            return false;
        }

        $this->session->remove(FrontendUser::SECURITY_SESSION_KEY);

        return true;
    }
}
