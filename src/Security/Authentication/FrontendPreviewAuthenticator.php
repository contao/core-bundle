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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\Authentication\Token\FrontendPreviewToken;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use Contao\StringUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendPreviewAuthenticator
{
    private Security $security;
    private TokenStorageInterface $tokenStorage;
    private TokenChecker $tokenChecker;
    private SessionInterface $session;
    private UserProviderInterface $userProvider;
    private ?LoggerInterface $logger;

    /**
     * @internal
     */
    public function __construct(Security $security, TokenStorageInterface $tokenStorage, TokenChecker $tokenChecker, SessionInterface $session, UserProviderInterface $userProvider, ?LoggerInterface $logger = null)
    {
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
        $this->tokenChecker = $tokenChecker;
        $this->session = $session;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function authenticateFrontendUser(string $username, bool $showUnpublished): bool
    {
        $user = $this->loadFrontendUser($username);

        if (null === $user) {
            return false;
        }

        $token = new FrontendPreviewToken($user, $showUnpublished);

        $this->updateToken($token);

        return true;
    }

    public function authenticateFrontendGuest(bool $showUnpublished, ?int $previewLinkId = null): bool
    {
        $token = new FrontendPreviewToken(null, $showUnpublished, $previewLinkId);

        $this->updateToken($token);

        return true;
    }

    /**
     * Removes a front end authentication from the session.
     */
    public function removeFrontendAuthentication(): bool
    {
        if (!$this->session->isStarted() || !$this->session->has('_security_contao_frontend')) {
            return false;
        }

        $this->updateToken(null);

        return true;
    }

    /**
     * Replaces the current token if the frontend firewall is active.
     * Otherwise, the token is stored in the session.
     */
    private function updateToken(?FrontendPreviewToken $token): void
    {
        if ($this->tokenChecker->isFrontendFirewall()) {
            $this->tokenStorage->setToken($token);
        } elseif (null === $token) {
            $this->session->remove('_security_contao_frontend');
        } else {
            $this->session->set('_security_contao_frontend', serialize($token));
        }
    }

    /**
     * Loads the front end user and checks its group access permissions.
     */
    private function loadFrontendUser(string $username): ?FrontendUser
    {
        try {
            $frontendUser = $this->userProvider->loadUserByIdentifier($username);

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

        $frontendGroups = StringUtil::deserialize($frontendUser->groups, true);

        // The front end user does not belong to a group that the back end user is allowed to log in
        if (!$this->security->isGranted('contao_user.amg', $frontendGroups)) {
            return null;
        }

        return $frontendUser;
    }
}
