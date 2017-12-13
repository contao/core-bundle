<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security;

use Contao\CoreBundle\Security\TokenChecker;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenCheckerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Security\TokenChecker', new TokenChecker($this->mockSession()));
    }

    public function testIsAuthenticatedFromTokenInSession()
    {
        // A mock object cannot be serialized
        $token = new PreAuthenticatedToken('foobar', null, 'foobar', ['foobar']);

        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('get')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(serialize($token))
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertTrue($tokenChecker->isAuthenticated(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testIsNotAuthenticatedIfSessionIsNotStarted()
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertFalse($tokenChecker->isAuthenticated(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testIsNotAuthenticatedIfSessionKeyDoesNotExist()
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(false)
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertFalse($tokenChecker->isAuthenticated(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testChecksIfSessionContainsAToken()
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('get')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(serialize(new \stdClass()))
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertFalse($tokenChecker->isAuthenticated(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testChecksIfTokenIsAuthenticated()
    {
        $token = $this->createMock(TokenInterface::class);

        $token
            ->method('isAuthenticated')
            ->willReturn(false)
        ;

        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('get')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(serialize($token))
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertFalse($tokenChecker->isAuthenticated(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testUsernameIsNullWithoutTokenInSession()
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertNull($tokenChecker->getUsername(FrontendUser::SECURITY_SESSION_KEY));
    }

    public function testUsernameFromTokenInSession()
    {
        // A mock object cannot be serialized
        $token = new PreAuthenticatedToken('foobar', null, 'foobar', ['foobar']);

        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('get')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(serialize($token))
        ;

        $tokenChecker = new TokenChecker($session);

        $this->assertSame('foobar', $tokenChecker->getUsername(FrontendUser::SECURITY_SESSION_KEY));
    }
}
