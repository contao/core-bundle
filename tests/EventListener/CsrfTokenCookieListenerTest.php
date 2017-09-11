<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use Contao\CoreBundle\EventListener\CsrfTokenCookieListener;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Tests the CsrfTokenCookieListener class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class CsrfTokenCookieListenerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(
            'Contao\CoreBundle\EventListener\CsrfTokenCookieListener',
            new CsrfTokenCookieListener($this->createMock(MemoryTokenStorage::class))
        );
    }

    /**
     * Tests the onKernelResponse() method.
     */
    public function testOnKernelRequest()
    {
        $request = $this->createMock(Request::class);

        $request->cookies = new ParameterBag([
            'csrf_foo' => 'bar',
            'not_csrf' => 'baz',
        ]);

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

        $tokenStorage = $this->createMock(MemoryTokenStorage::class);

        $tokenStorage
            ->expects($this->once())
            ->method('initialize')
            ->with(['foo' => 'bar'])
        ;

        $listener = new CsrfTokenCookieListener($tokenStorage);

        $listener->onKernelRequest($requestEvent);
    }

    /**
     * Tests the onKernelResponse() method.
     */
    public function testOnKernelResponse()
    {
        $request = $this->createMock(Request::class);

        $request
            ->expects($this->any())
            ->method('isSecure')
            ->willReturn(true)
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
            ->method('getRequest')
            ->willReturn($request)
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
                function (Cookie $cookie) {
                    $this->assertSame('csrf_foo', $cookie->getName());
                    $this->assertSame('bar', $cookie->getValue());
                    $this->assertSame('/', $cookie->getPath());
                    $this->assertTrue($cookie->isHttpOnly());
                    $this->assertTrue($cookie->isSecure());
                    $this->assertSame('lax', $cookie->getSameSite());

                    return true;
                }
            ))
        ;

        $response->headers = $responseHeaders;

        $tokenStorage = $this->createMock(MemoryTokenStorage::class);

        $tokenStorage
            ->expects($this->once())
            ->method('getSaveTokens')
            ->willReturn(['foo' => 'bar'])
        ;

        $listener = new CsrfTokenCookieListener($tokenStorage);

        $listener->onKernelResponse($responseEvent);
    }
}
