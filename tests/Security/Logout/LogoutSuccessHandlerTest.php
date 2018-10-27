<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Security\Logout;

use Contao\CoreBundle\Security\Logout\LogoutSuccessHandler;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class LogoutSuccessHandlerTest extends TestCase
{
    public function testRedirectsToAGivenUrl(): void
    {
        $request = new Request();
        $request->query->set('redirect', 'http://localhost/home');

        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($request, 'http://localhost/home')
            ->willReturn(new RedirectResponse('http://localhost/home'))
        ;

        $handler = new LogoutSuccessHandler($httpUtils);

        /** @var RedirectResponse $response */
        $response = $handler->onLogoutSuccess($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://localhost/home', $response->getTargetUrl());
    }

    public function testRedirectsToTheRefererUrl(): void
    {
        $request = new Request();
        $request->headers->set('Referer', 'http://localhost/home');

        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($request, 'http://localhost/home')
            ->willReturn(new RedirectResponse('http://localhost/home'))
        ;

        $handler = new LogoutSuccessHandler($httpUtils);

        /** @var RedirectResponse $response */
        $response = $handler->onLogoutSuccess($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://localhost/home', $response->getTargetUrl());
    }

    public function testRedirectsToTheDefaultUrl(): void
    {
        $request = new Request();
        $httpUtils = $this->createMock(HttpUtils::class);
        $httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($request, '/')
            ->willReturn(new RedirectResponse('http://localhost'))
        ;

        $handler = new LogoutSuccessHandler($httpUtils);

        /** @var RedirectResponse $response */
        $response = $handler->onLogoutSuccess($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('http://localhost', $response->getTargetUrl());
    }
}
