<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Security\Authentication;

use Contao\CoreBundle\Security\Authentication\AuthenticationFailureHandler;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Tests the AuthenticationFailureHandler class.
 */
class AuthenticationFailureHandlerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $httpKernel = $this->createMock(HttpKernel::class);
        $httpUtils = $this->createMock(HttpUtils::class);

        $handler = new AuthenticationFailureHandler($httpKernel, $httpUtils);

        $this->assertInstanceOf('Contao\CoreBundle\Security\Authentication\AuthenticationFailureHandler', $handler);
    }

    /**
     * Tests the redirect on authentication failure.
     */
    public function testRedirectsOnAuthenticationFailure(): void
    {
        $httpKernel = $this->createMock(HttpKernel::class);
        $httpUtils = $this->createMock(HttpUtils::class);

        $request = new Request();
        $session = $this->mockSession();

        $authenticationException = new AuthenticationException();

        $request->setSession($session);
        $request->headers->set('referer', '/');

        $handler = new AuthenticationFailureHandler($httpKernel, $httpUtils);

        $response = $handler->onAuthenticationFailure($request, $authenticationException);
        $authenticationError = $request->getSession()->get(Security::AUTHENTICATION_ERROR);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertInstanceOf('Symfony\Component\Security\Core\Exception\AuthenticationException', $authenticationError);
        $this->assertTrue($response->headers->contains('location', '/'));
    }
}
