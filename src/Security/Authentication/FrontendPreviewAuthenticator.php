<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\BackendUser;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\Authentication\Token\FrontendPreviewToken;
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

    public function __construct(SessionInterface $session, TokenStorageInterface $tokenStorage, UserProviderInterface $userProvider, LoggerInterface $logger = null)
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function authenticateFrontendUser(string $username, bool $showUnpublished): bool
    {
        $backendUser = $this->loadBackendUser();

        if (null === $backendUser) {
            return false;
        }

        // The back end user does not have permission to log in front end users
        if (!$backendUser->isAdmin && (empty($backendUser->amg) || !\is_array($backendUser->amg))) {
            return false;
        }

        $frontendUser = $this->loadFrontendUser($username, $backendUser);

        if (null === $frontendUser) {
            return false;
        }

        $token = new FrontendPreviewToken($frontendUser, $showUnpublished);

        $this->session->set(FrontendUser::SECURITY_SESSION_KEY, serialize($token));

        return true;
    }

    public function authenticateFrontendGuest(bool $showUnpublished): bool
    {
        $backendUser = $this->loadBackendUser();

        if (null === $backendUser) {
            return false;
        }

        $token = new FrontendPreviewToken(null, $showUnpublished);

        $this->session->set(FrontendUser::SECURITY_SESSION_KEY, serialize($token));

        return true;
    }

    /**
     * Removes a front end authentication from the session.
     */
    public function removeFrontendAuthentication(): bool
    {
        if (!$this->session->isStarted() || !$this->session->has(FrontendUser::SECURITY_SESSION_KEY)) {
            return false;
        }

        $this->session->remove(FrontendUser::SECURITY_SESSION_KEY);

        return true;
    }

    private function loadBackendUser(): ?BackendUser
    {
        if (!$this->session->isStarted()) {
            return null;
        }

        $token = $this->tokenStorage->getToken();

        // Check if a back end user is authenticated
        if (null === $token || !$token->isAuthenticated()) {
            return null;
        }

        $backendUser = $token->getUser();

        if (!$backendUser instanceof BackendUser) {
            return null;
        }

        return $backendUser;
    }

    /**
     * Loads the front end user and checks its group access permissions.
     */
    private function loadFrontendUser(string $username, BackendUser $backendUser): ?FrontendUser
    {
        try {
            $frontendUser = $this->userProvider->loadUserByUsername($username);

            // Make sure the user provider returned a front end user
            if (!$frontendUser instanceof FrontendUser) {
                throw new UsernameNotFoundException('User is not a front end user');
            }
        } catch (UsernameNotFoundException $e) {
            if (null !== $this->logger) {
                $this->logger->info(
                    sprintf('Could not find a front end user with the username "%s"', $username),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, '')]
                );
            }

            return null;
        }

        $allowedGroups = StringUtil::deserialize($backendUser->amg, true);
        $frontendGroups = StringUtil::deserialize($frontendUser->groups, true);

        // The front end user does not belong to a group that the back end user is allowed to log in
        if (!$backendUser->isAdmin && !\count(array_intersect($frontendGroups, $allowedGroups))) {
            return null;
        }

        return $frontendUser;
    }
}
