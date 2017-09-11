<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Csrf;

use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * Token storage that uses the memory to store the tokens.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class MemoryTokenStorage implements TokenStorageInterface
{
    /**
     * @var array
     */
    private $tokens;

    /**
     * @var array
     */
    private $activeTokens = [];

    /**
     * {@inheritdoc}
     */
    public function getToken($tokenId)
    {
        $this->assertInitialized();

        if (empty($this->tokens[$tokenId])) {
            throw new TokenNotFoundException('The CSRF token with ID '.$tokenId.' does not exist.');
        }

        $this->activeTokens[$tokenId] = true;

        return $this->tokens[$tokenId];
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($tokenId, $token)
    {
        $this->assertInitialized();

        $this->activeTokens[$tokenId] = true;
        $this->tokens[$tokenId] = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken($tokenId)
    {
        $this->assertInitialized();

        return !empty($this->tokens[$tokenId]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken($tokenId)
    {
        $this->assertInitialized();

        $this->activeTokens[$tokenId] = true;
        $this->tokens[$tokenId] = null;
    }

    /**
     * Initialize the storage.
     *
     * @param array $tokens
     */
    public function initialize(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Get all active tokens.
     *
     * @return array
     */
    public function getSaveTokens()
    {
        $tokens = [];

        foreach (array_keys($this->activeTokens) as $key) {
            $tokens[$key] = $this->tokens[$key];
        }

        return $tokens;
    }

    /**
     * Check if the store is initialized.
     *
     * @throws \LogicException if the store was not initialized
     */
    private function assertInitialized()
    {
        if (null === $this->tokens) {
            throw new \LogicException('MemoryTokenStorage must not be accessed before it was initialized.');
        }
    }
}
