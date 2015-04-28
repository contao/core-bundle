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
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Tests the ContaoToken class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoTokenTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $token = new ContaoToken($this->mockFrontendUser());

        $this->assertInstanceOf('Contao\\CoreBundle\\Security\\Authentication\\ContaoToken', $token);
    }

    /**
     * Tests a front end user.
     */
    public function testFrontendUser()
    {
        $token = new ContaoToken($this->mockFrontendUser());

        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals('', $token->getCredentials());

        $this->assertEquals(
            [
                new Role('ROLE_MEMBER'),
            ],
            $token->getRoles()
        );
    }

    /**
     * Tests a back end user.
     */
    public function testBackendUser()
    {
        $token = new ContaoToken($this->mockBackendUser());

        $this->assertTrue($token->isAuthenticated());
        $this->assertEquals('', $token->getCredentials());

        $this->assertEquals(
            [
                new Role('ROLE_USER'),
                new Role('ROLE_ADMIN'),
            ],
            $token->getRoles()
        );
    }

    /**
     * Tests an unauthenticated user.
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testUnauthenticatedUser()
    {
        $user = $this->getMock('Contao\\CoreBundle\\Adapter\\FrontendUserAdapterInterface');
        $user->expects($this->once())->method('authenticate')->willReturn(false);

        new ContaoToken($user);
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
        $user->expects($this->any())->method('getValue')->with($this->equalTo('isAdmin'))->willReturn(true);

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
