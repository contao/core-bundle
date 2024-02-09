<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
class CsrfTokenCookieSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContaoCsrfTokenManager $tokenManager,
        private readonly MemoryTokenStorage $tokenStorage,
        private readonly string $cookiePrefix = 'csrf_',
    ) {
    }

    /**
     * Reads the cookies from the request and injects them into the storage.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->tokenStorage->initialize($this->getTokensFromCookies($event->getRequest()->cookies));
    }

    /**
     * Adds the token cookies to the response.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (false === $request->attributes->get('_token_check')) {
            return;
        }

        $response = $event->getResponse();

        if ($this->requiresCsrf($request, $response)) {
            $this->setCookies($request, $response);
        } elseif ($response->isSuccessful()) {
            // Only delete the CSRF token cookie if the response is successful (#2252)
            $this->removeCookies($request, $response);
            $this->replaceTokenOccurrences($response);
        }
    }

    /**
     * Initializes an empty CSRF token storage for the command line.
     */
    public function onCommand(ConsoleCommandEvent $event): void
    {
        $this->tokenStorage->initialize([]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // The priority must be higher than the one of the Symfony route listener
            // (defaults to 32)
            KernelEvents::REQUEST => ['onKernelRequest', 36],
            // The priority must be higher than the one of the make-response-private listener
            // (defaults to -896)
            KernelEvents::RESPONSE => ['onKernelResponse', -832],
            ConsoleEvents::COMMAND => ['onCommand', 36],
        ];
    }

    private function requiresCsrf(Request $request, Response $response): bool
    {
        foreach ($request->cookies as $key => $value) {
            if (!$this->isCsrfCookie($key, $value)) {
                return true;
            }
        }

        if ($response->headers->getCookies(ResponseHeaderBag::COOKIES_ARRAY)) {
            return true;
        }

        if ($request->getUserInfo()) {
            return true;
        }

        return $request->hasSession() && !$this->isSessionEmpty($request->getSession());
    }

    private function isSessionEmpty(SessionInterface $session): bool
    {
        if (!$session->isStarted()) {
            return true;
        }

        if ($session instanceof Session) {
            // Marked @internal but no other way to check all attribute bags
            return $session->isEmpty();
        }

        return [] === $session->all();
    }

    private function setCookies(Request $request, Response $response): void
    {
        $isSecure = $request->isSecure();
        $basePath = $request->getBasePath() ?: '/';

        foreach ($this->tokenStorage->getUsedTokens() as $key => $value) {
            $cookieKey = $this->cookiePrefix.$key;

            // The cookie already exists
            if ($request->cookies->has($cookieKey) && $value === $request->cookies->get($cookieKey)) {
                continue;
            }

            $cookie = new Cookie(
                $cookieKey,
                $value,
                null === $value ? 1 : 0,
                $basePath,
                null,
                $isSecure,
                true,
                false,
                Cookie::SAMESITE_LAX,
            );

            $response->headers->setCookie($cookie);
        }
    }

    private function replaceTokenOccurrences(Response $response): void
    {
        // Return if the response is not an HTML document
        if (false === stripos((string) $response->headers->get('Content-Type'), 'text/html')) {
            return;
        }

        $content = $response->getContent();
        $tokens = $this->tokenManager->getUsedTokenValues();

        if (!$tokens || !\is_string($content)) {
            return;
        }

        $content = str_replace($tokens, '', $content, $replacedCount);

        if ($replacedCount <= 0) {
            return;
        }

        $response->setContent($content);

        // Remove the Content-Length header now that we have changed the content length (see
        // #2416). Do not add the header or adjust an existing one (see symfony/symfony#1846).
        $response->headers->remove('Content-Length');
    }

    private function removeCookies(Request $request, Response $response): void
    {
        $isSecure = $request->isSecure();
        $basePath = $request->getBasePath() ?: '/';

        foreach ($request->cookies as $key => $value) {
            if ($this->isCsrfCookie($key, $value)) {
                $response->headers->clearCookie($key, $basePath, null, $isSecure);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function getTokensFromCookies(ParameterBag $cookies): array
    {
        $tokens = [];

        foreach ($cookies as $key => $value) {
            if ($this->isCsrfCookie($key, $value)) {
                $tokens[substr($key, \strlen($this->cookiePrefix))] = $value;
            }
        }

        return $tokens;
    }

    private function isCsrfCookie(mixed $key, mixed $value): bool
    {
        if (!\is_string($key)) {
            return false;
        }

        return str_starts_with($key, $this->cookiePrefix) && preg_match('/^[a-z0-9_-]+$/i', (string) $value);
    }
}
