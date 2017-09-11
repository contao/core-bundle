<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Csrf;

use Contao\CoreBundle\Csrf\CookieTokenStorage;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Tests the PictureFactory class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class CookieTokenStorageTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $cookieTokenStorage = new CookieTokenStorage();

        $this->assertInstanceOf('Contao\CoreBundle\Csrf\CookieTokenStorage', $cookieTokenStorage);
        $this->assertInstanceOf('Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface', $cookieTokenStorage);
    }

    /**
     */
    public function testStoresTokensAsCookies()
    {
        $request = $this->createMock(Request::class);

        $request->cookies = new ParameterBag();

        $requestEvent = $this->createMock(GetResponseEvent::class);

        $requestEvent
            ->expects($this->any())
            ->method('isMasterRequest')
            ->willReturn(true)
        ;

        $requestEvent
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($request)
        ;

        $response = $this->createMock(Response::class);

        $responseEvent = $this->createMock(FilterResponseEvent::class);

        $responseEvent
            ->expects($this->any())
            ->method('isMasterRequest')
            ->willReturn(true)
        ;

        $responseEvent
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($response)
        ;

        $responseHeaders = $this->createMock(ResponseHeaderBag::class);

        $responseHeaders
            ->expects($this->once())
            ->method('setCookie')
            ->with($this->callback(
                function(Cookie $cookie) {
                    $this->assertSame('csrf_foo', $cookie->getName());
                    $this->assertSame('bar', $cookie->getValue());
                    $this->assertSame('/', $cookie->getPath());
                    $this->assertTrue($cookie->isHttpOnly());
                    $this->assertSame('lax', $cookie->getSameSite());

                    return true;
                }
            ))
        ;

        $response->headers = $responseHeaders;

        $cookieTokenStorage = new CookieTokenStorage();

        $cookieTokenStorage->onKernelRequest($requestEvent);

        $this->assertFalse($cookieTokenStorage->hasToken('foo'));

        $cookieTokenStorage->setToken('foo', 'bar');

        $this->assertTrue($cookieTokenStorage->hasToken('foo'));
        $this->assertSame('bar', $cookieTokenStorage->getToken('foo'));

        $cookieTokenStorage->onKernelResponse($responseEvent);
    }
}
