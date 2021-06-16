<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Csrf;

use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Contracts\Service\ResetInterface;

class MemoryTokenStorage implements TokenStorageInterface, ResetInterface
{
    /**
     * @var array|null
     */
    private $tokens;

    /**
     * @var array
     */
    private $usedTokens = [];

    public function getToken($tokenId): string
    {
        $this->assertInitialized();

        if (empty($this->tokens[$tokenId])) {
            throw new TokenNotFoundException(sprintf('The CSRF token ID "%s" does not exist.', $tokenId));
        }

        $this->usedTokens[$tokenId] = true;

        return $this->tokens[$tokenId];
    }

    public function setToken($tokenId, $token): void
    {
        $this->assertInitialized();

        $this->usedTokens[$tokenId] = true;
        $this->tokens[$tokenId] = $token;
    }

    public function hasToken($tokenId): bool
    {
        $this->assertInitialized();

        return !empty($this->tokens[$tokenId]);
    }

    public function removeToken($tokenId): ?string
    {
        $this->assertInitialized();

        $token = null;

        if (isset($this->tokens[$tokenId])) {
            $token = $this->tokens[$tokenId];
            $this->tokens[$tokenId] = null;
        }

        $this->usedTokens[$tokenId] = true;

        return $token;
    }

    public function initialize(array $tokens): void
    {
        $this->tokens = $tokens;
    }

    public function getUsedTokens(): array
    {
        if (null === $this->tokens) {
            return [];
        }

        return array_intersect_key($this->tokens, $this->usedTokens);
    }

    public function reset(): void
    {
        $this->tokens = null;
        $this->usedTokens = [];
    }

    /**
     * @throws \LogicException
     */
    private function assertInitialized(): void
    {
        if (null === $this->tokens) {
            throw new \LogicException('MemoryTokenStorage must not be accessed before it was initialized.');
        }
    }
}
