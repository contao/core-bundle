<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\User;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\User\BackendUserProvider;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Tests the BackendUserProvider class.
 */
class BackendUserProviderTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $provider = new BackendUserProvider($this->mockContaoFramework());

        $this->assertInstanceOf('Contao\CoreBundle\Security\User\BackendUserProvider', $provider);
    }

    /**
     * Tests loading an existing backend user.
     */
    public function testCanLoadExistingBackendUserByUsername(): void
    {
        $user = $this
            ->getMockBuilder(BackendUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsername'])
            ->getMock()
        ;

        $user
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('test-user')
        ;

        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadUserByUsername'])
            ->getMock()
        ;

        $adapter
            ->expects($this->exactly(1))
            ->method('loadUserByUsername')
            ->with('test-user')
            ->willReturn($user)
        ;

        $framework = $this->mockContaoFramework([BackendUser::class => $adapter]);
        $provider = new BackendUserProvider($framework);

        $this->assertInstanceOf('Contao\BackendUser', $provider->loadUserByUsername('test-user'));
    }

    /**
     * Tests if a supported backend user can be refreshed.
     */
    public function testCanRefreshASupportedBackendUser(): void
    {
        $user = $this
            ->getMockBuilder(BackendUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsername'])
            ->getMock()
        ;

        $user
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('test-user')
        ;

        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadUserByUsername'])
            ->getMock()
        ;

        $adapter
            ->expects($this->exactly(1))
            ->method('loadUserByUsername')
            ->with('test-user')
            ->willReturn($user)
        ;

        $framework = $this->mockContaoFramework([BackendUser::class => $adapter]);
        $provider = new BackendUserProvider($framework);

        $this->assertInstanceOf('Contao\BackendUser', $provider->refreshUser($user));
    }

    /**
     * Tests loading a non-existing backend user.
     */
    public function testCanNotLoadNonExistingBackendUserByUsername(): void
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadUserByUsername'])
            ->getMock()
        ;

        $adapter
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('test-user')
            ->willReturn(null)
        ;

        $framework = $this->mockContaoFramework([BackendUser::class => $adapter]);

        $provider = new BackendUserProvider($framework);

        $this->expectException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException');
        $provider->loadUserByUsername('test-user');
    }

    /**
     * Tests loading a not supported user.
     */
    public function testCanNotLoadNonSupportedUser(): void
    {
        $provider = new BackendUserProvider($this->mockContaoFramework());

        $this->expectException('Symfony\Component\Security\Core\Exception\UnsupportedUserException');
        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    /**
     * Tests supporting only the BackendUser class.
     */
    public function testSupportsOnlyBackendUserClass(): void
    {
        $provider = new BackendUserProvider($this->mockContaoFramework());

        $this->assertTrue($provider->supportsClass('Contao\BackendUser'));
        $this->assertFalse($provider->supportsClass('Contao\FrontendUser'));
    }
}
