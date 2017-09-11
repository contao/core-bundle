<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Retrieves and stores CSRF token cookies.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class CsrfTokenCookieListener
{
    /**
     * @var MemoryTokenStorage
     */
    private $tokenStorage;

    /**
     * @var int
     */
    private $cookieLifetime;

    /**
     * @var string
     */
    private $cookiePrefix;

    /**
     * @var bool
     */
    private $isSecureRequest;

    /**
     * Constructor.
     *
     * @param MemoryTokenStorage $tokenStorage
     * @param int                $cookieLifetime
     * @param string             $cookiePrefix
     */
    public function __construct(MemoryTokenStorage $tokenStorage, int $cookieLifetime = 86400, string $cookiePrefix = 'csrf_')
    {
        $this->tokenStorage = $tokenStorage;
        $this->cookieLifetime = $cookieLifetime;
        $this->cookiePrefix = $cookiePrefix;
    }

    /**
     * Reads the cookies from the request and injects them into the storage.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->tokenStorage->initialize($this->getTokensFromCookies($event->getRequest()->cookies));
        $this->isSecureRequest = $event->getRequest()->isSecure();
    }

    /**
     * Writes the current session data to the database.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $cookieLifetime = $this->cookieLifetime ? $this->cookieLifetime + time() : 0;

        foreach ($this->tokenStorage->getActiveTokens() as $key => $value) {
            $event->getResponse()->headers->setCookie(
                new Cookie($this->cookiePrefix.$key, $value, $cookieLifetime, '/', null, $this->isSecureRequest, true, false, Cookie::SAMESITE_LAX)
            );
        }
    }

    /**
     * Get the token array from the cookies.
     *
     * @param ParameterBag $cookies
     *
     * @return array
     */
    private function getTokensFromCookies(ParameterBag $cookies)
    {
        $tokens = [];

        foreach ($cookies as $key => $value) {
            if (strncmp($key, $this->cookiePrefix, strlen($this->cookiePrefix)) === 0) {
                $tokens[substr($key, strlen($this->cookiePrefix))] = $value;
            }
        }

        return $tokens;
    }
}
