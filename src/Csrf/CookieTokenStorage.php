<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Csrf;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * Token storage that uses a cookie to store the token.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class CookieTokenStorage implements TokenStorageInterface
{
    /**
     * The namespace used to store the cookie.
     *
     * @var string
     */
    const SESSION_NAMESPACE = 'csrf_';

    /**
     * @var string
     */
    private $namespace;

    /**
     * The cookies to be set on the response.
     *
     * @var array
     */
    private $cookies;

    /**
     * If the master request is HTTPS.
     *
     * @var bool
     */
    private $isSecureRequest;

    /**
     * Initializes the storage with a cookie namespace.
     *
     * @param string $namespace the namespace under which the token cookie is stored
     */
    public function __construct(string $namespace = self::SESSION_NAMESPACE)
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken($tokenId)
    {
        $this->assertRequest();

        if (empty($this->cookies[$tokenId])) {
            throw new TokenNotFoundException('The CSRF token with ID '.$tokenId.' does not exist.');
        }

        return $this->cookies[$tokenId];
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($tokenId, $token)
    {
        $this->assertRequest();

        $this->cookies[$tokenId] = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken($tokenId)
    {
        $this->assertRequest();

        return !empty($this->cookies[$tokenId]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeToken($tokenId)
    {
        $this->assertRequest();

        $this->cookies[$tokenId] = '';
    }

    /**
     * Get and store the cookies from the master request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->cookies = $this->getCookiesFromRequest($event->getRequest());
        $this->isSecureRequest = $event->getRequest()->isSecure();
    }

    /**
     * Set the cookies on the master response.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $cookieLifetime = (int) ini_get('session.cookie_lifetime');
        if ($cookieLifetime) {
            $cookieLifetime += time();
        }

        foreach ($this->cookies as $key => $value) {
            $event->getResponse()->headers->setCookie(
                new Cookie($this->namespace.$key, $value, $cookieLifetime, '/', null, $this->isSecureRequest, true, false, Cookie::SAMESITE_LAX)
            );
        }
    }

    /**
     * Check if the kernel.request event did happen already.
     *
     * @throws \LogicException If the kernel.request event was not triggered.
     */
    private function assertRequest()
    {
        if (null === $this->cookies) {
            throw new \LogicException('CookieTokenStorage must not be accessed before the kernel.request event.');
        }
    }

    /**
     * Get the cookie array from the request.
     *
     * @param Request $request
     *
     * @return array
     */
    private function getCookiesFromRequest(Request $request)
    {
        $cookies = [];

        foreach ($request->cookies as $key => $value) {
            if (strncmp($key, $this->namespace, strlen($this->namespace)) === 0) {
                $cookies[substr($key, strlen($this->namespace))] = $value;
            }
        }

        return $cookies;
    }
}
