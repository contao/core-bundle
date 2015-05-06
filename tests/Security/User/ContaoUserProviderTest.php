<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\Authentication;

use Terminal42\ContaoAdapterBundle\Adapter\FrontendUserAdapter;
use Contao\CoreBundle\Security\User\ContaoUserProvider;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\Security\Core\User\User;

/**
 * Tests the ContaoUserProvider class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoUserProviderTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $provider = $this->getUserProvider();

        $this->assertInstanceOf('Contao\\CoreBundle\\Security\\User\\ContaoUserProvider', $provider);
    }

    /**
     * Tests loading the user "backend".
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadUserBackend()
    {
        $provider = $this->getUserProvider();

        $this->assertInstanceOf('Contao\\CoreBundle\\Adapter\\BackendUserAdapter', $provider->loadUserByUsername('backend'));
    }

    /**
     * Tests loading the user "frontend".
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadUserFrontend()
    {
        $provider = $this->getUserProvider();

        $this->assertInstanceOf('Contao\\CoreBundle\\Adapter\\FrontendUserAdapter', $provider->loadUserByUsername('frontend'));
    }

    /**
     * Tests an unsupported username.
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUnsupportedUsername()
    {
        $provider = $this->getUserProvider();
        $provider->loadUserByUsername('foo');
    }

    /**
     * Tests refreshing a user.
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUser()
    {
        $provider = $this->getUserProvider();

        $provider->refreshUser(new User('foo', 'bar'));
    }

    /**
     * Tests the supportsClass method.
     */
    public function testSupportsClass()
    {
        $provider = $this->getUserProvider();
        $frontendUserAdapter = new FrontendUserAdapter();

        $this->assertTrue($provider->supportsClass($frontendUserAdapter));
    }


    /**
     * Mock front end user adapter
     */
    private function mockFrontendUser()
    {
        $user = $this->getMock('Contao\\CoreBundle\\Adapter\\FrontendUserAdapter');
        $user->expects($this->any())->method('instantiate')->willReturnSelf();
        $user->expects($this->any())->method('authenticate')->willReturn(true);

        return $user;
    }

    /**
     * Mock back end user adapter
     */
    private function mockBackendUser()
    {
        $user = $this->getMock('Contao\\CoreBundle\\Adapter\\BackendUserAdapter');
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
