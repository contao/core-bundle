<?php

namespace Contao\CoreBundle\Security\Authentication;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ContaoPreviewAuthenticator
{
    protected $requestStack;
    protected $session;
    protected $tokenStorage;
    protected $userProvider;
    protected $logger;

    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        UserProviderInterface $userProvider,
        LoggerInterface $logger = null
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function authenticateFrontend($username = null)
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
        } catch(UsernameNotFoundException $e) {
            return;
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

            if (null !== $this->logger) {
                $this->logger->debug('Stored the security token in the session.', array('key' => $sessionKey));
            }
        }
    }
}
