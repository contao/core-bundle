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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenChecker
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Checks if an authenticated token exists in the session.
     *
     * @param string $sessionKey
     *
     * @return bool
     */
    public function isAuthenticated(string $sessionKey): bool
    {
        return null !== $this->getToken($sessionKey);
    }

    /**
     * Gets the username of a token in the session.
     *
     * @param string $sessionKey
     *
     * @return string|null
     */
    public function getUsername(string $sessionKey): ?string
    {
        $token = $this->getToken($sessionKey);

        if (null === $token) {
            return null;
        }

        return $token->getUsername();
    }

    /**
     * Gets the token from the session storage.
     *
     * @param string $sessionKey
     *
     * @return TokenInterface|null
     */
    private function getToken(string $sessionKey): ?TokenInterface
    {
        if (!$this->session->isStarted() || !$this->session->has($sessionKey)) {
            return null;
        }

        $token = unserialize($this->session->get($sessionKey), ['allowed_classes' => true]);

        if (!$token instanceof TokenInterface || !$token->isAuthenticated()) {
            return null;
        }

        return $token;
    }
}
