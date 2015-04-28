<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\Authentication;

use Contao\CoreBundle\Security\Authentication\ContaoToken;
use Contao\CoreBundle\Security\ContaoAuthenticator;
use Contao\CoreBundle\Security\User\ContaoUserProvider;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * Tests the ContaoAuthenticator class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoAuthenticatorTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $authenticator = new ContaoAuthenticator($this->getUserProvider());

        $this->assertInstanceOf('Contao\\CoreBundle\\Security\\ContaoAuthenticator', $authenticator);
    }

    /**
     * Tests creating an authentication token.
     */
    public function testCreateToken()
    {
        $authenticator = new ContaoAuthenticator($this->getUserProvider());
        $token         = $authenticator->createToken(new Request(), 'frontend');

        $this->assertInstanceOf('Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken', $token);
        $this->assertEquals('frontend', $token->getKey());
        $this->assertEquals('anon.', $token->getUsername());
    }

    /**
     * Tests authenticating a token.
     */
    public function testAuthenticateToken()
    {
        $authenticator = new ContaoAuthenticator($this->getUserProvider());

        $this->assertInstanceOf(
            'Contao\\CoreBundle\\Security\\Authentication\\ContaoToken',
            $authenticator->authenticateToken(new ContaoToken($this->mockFrontendUser()), $this->getUserProvider(), 'frontend')
        );

        $this->assertInstanceOf(
            'Contao\\CoreBundle\\Security\\Authentication\\ContaoToken',
            $authenticator->authenticateToken(new AnonymousToken('frontend', 'anon.'), $this->getUserProvider(), 'frontend')
        );

        $this->assertEquals(
            new AnonymousToken('console', 'anon.'),
            $authenticator->authenticateToken(new AnonymousToken('console', 'anon.'), $this->getUserProvider(), 'console')
        );
    }

    /**
     * Tests authenticating an invalid token.
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateInvalidToken()
    {
        $authenticator = new ContaoAuthenticator($this->getUserProvider());
        $authenticator->authenticateToken(new PreAuthenticatedToken('foo', 'bar', 'console'), $this->getUserProvider(), 'console');
    }

    /**
     * Tests the token support.
     */
    public function testSupportsToken()
    {
        $authenticator = new ContaoAuthenticator($this->getUserProvider());

        $this->assertTrue($authenticator->supportsToken(new ContaoToken($this->mockFrontendUser()), 'frontend'));
        $this->assertTrue($authenticator->supportsToken(new AnonymousToken('anon.', 'foo'), 'frontend'));
        $this->assertFalse($authenticator->supportsToken(new PreAuthenticatedToken('foo', 'bar', 'console'), 'console'));
    }

    /**
     * Mock front end user adapter
     */
    private function mockFrontendUser()
    {
        $user = $this->getMock('Contao\\CoreBundle\\Adapter\\FrontendUserAdapterInterface');
        $user->expects($this->any())->method('instantiate')->willReturnSelf();
        $user->expects($this->any())->method('authenticate')->willReturn(true);

        return $user;
    }

    /**
     * Mock back end user adapter
     */
    private function mockBackendUser()
    {
        $user = $this->getMock('Contao\\CoreBundle\\Adapter\\BackendUserAdapterInterface');
        $user->expects($this->any())->method('instantiate')->willReturnSelf();
        $user->expects($this->any())->method('authenticate')->willReturn(true);

        return $user;
    }

    /**
     * Get user provider
     *
     * @return ContaoUserProvider
     */
    private function getUserProvider()
    {
        return new ContaoUserProvider(
            $this->mockFrontendUser(),
            $this->mockBackendUser()
        );
    }
}
