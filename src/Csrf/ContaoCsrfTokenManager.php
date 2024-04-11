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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Contracts\Service\ResetInterface;

class ContaoCsrfTokenManager extends CsrfTokenManager implements ResetInterface
{
    /**
     * @var array<int, string>
     */
    private array $usedTokenValues = [];

    /**
     * @var array<string, CsrfToken>
     */
    private array $tokenCache = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $csrfCookiePrefix,
        TokenGeneratorInterface|null $generator = null,
        TokenStorageInterface|null $storage = null,
        RequestStack|callable|string|null $namespace = null,
        private readonly string|null $defaultTokenName = null,
    ) {
        parent::__construct($generator, $storage, $namespace);
    }

    /**
     * @return array<int, string>
     */
    public function getUsedTokenValues(): array
    {
        return $this->usedTokenValues;
    }

    public function getToken($tokenId): CsrfToken
    {
        $this->tokenCache[$tokenId] ??= parent::getToken($tokenId);
        $this->usedTokenValues[] = $this->tokenCache[$tokenId]->getValue();

        return $this->tokenCache[$tokenId];
    }

    public function refreshToken($tokenId): CsrfToken
    {
        $this->tokenCache[$tokenId] = parent::refreshToken($tokenId);
        $this->usedTokenValues[] = $this->tokenCache[$tokenId]->getValue();

        return $this->tokenCache[$tokenId];
    }

    public function removeToken(string $tokenId): string|null
    {
        unset($this->tokenCache[$tokenId]);

        return parent::removeToken($tokenId);
    }

    public function isTokenValid(CsrfToken $token): bool
    {
        if (
            ($request = $this->requestStack->getCurrentRequest())
            && 'POST' === $request->getRealMethod()
            && $this->canSkipTokenValidation($request, $this->csrfCookiePrefix.$token->getId())
        ) {
            return true;
        }

        return parent::isTokenValid($token);
    }

    /**
     * Skip the CSRF token validation if the request has no cookies, no authenticated
     * user and the session has not been started.
     */
    public function canSkipTokenValidation(Request $request, string $tokenCookieName): bool
    {
        return
            !$request->getUserInfo()
            && (
                0 === $request->cookies->count()
                || [$tokenCookieName] === $request->cookies->keys()
            )
            && $this->isSessionEmpty($request);
    }

    public function getDefaultTokenValue(): string
    {
        if (null === $this->defaultTokenName) {
            throw new \RuntimeException('The Contao CSRF token manager was not initialized with a default token name.');
        }

        return $this->getToken($this->defaultTokenName)->getValue();
    }

    public function reset(): void
    {
        $this->usedTokenValues = [];
        $this->tokenCache = [];
    }

    private function isSessionEmpty(Request $request): bool
    {
        if (!$request->hasSession()) {
            return true;
        }

        $session = $request->getSession();

        if (!$session->isStarted()) {
            return true;
        }

        if ($session instanceof Session) {
            // Marked @internal but no other way to check all attribute bags
            return $session->isEmpty();
        }

        return [] === $session->all();
    }
}
