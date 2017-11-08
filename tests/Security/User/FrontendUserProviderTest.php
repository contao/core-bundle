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

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\User\FrontendUserProvider;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Tests the FrontendUserProvider class.
 */
class FrontendUserProviderTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $provider = new FrontendUserProvider($this->mockContaoFramework());

        $this->assertInstanceOf('Contao\CoreBundle\Security\User\FrontendUserProvider', $provider);
    }

    /**
     * Tests loading an existing frontend user.
     */
    public function testCanLoadExistingFrontendUserByUsername(): void
    {
        $user = $this
            ->getMockBuilder(FrontendUser::class)
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
            ->expects($this->exactly(2))
            ->method('loadUserByUsername')
            ->with('test-user')
            ->willReturn($user)
        ;

        $framework = $this->mockContaoFramework([FrontendUser::class => $adapter]);
        $provider = new FrontendUserProvider($framework);

        $this->assertInstanceOf(FrontendUser::class, $provider->loadUserByUsername('test-user'));
        $this->assertInstanceOf(FrontendUser::class, $provider->refreshUser($user));
    }

    /**
     * Tests loading a non-existing frontend user.
     */
    public function testCanNotLoadNonExistingFrontendUserByUsername(): void
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

        $framework = $this->mockContaoFramework([FrontendUser::class => $adapter]);

        $provider = new FrontendUserProvider($framework);

        $this->expectException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException');
        $provider->loadUserByUsername('test-user');
    }

    /**
     * Tests loading a not supported user.
     */
    public function testCanNotLoadNonSupportedUser(): void
    {
        $provider = new FrontendUserProvider($this->mockContaoFramework());

        $this->expectException('Symfony\Component\Security\Core\Exception\UnsupportedUserException');
        $provider->refreshUser($this->createMock(UserInterface::class));
    }

    /**
     * Tests supporting only the FrontendUser class.
     */
    public function testSupportsOnlyBackendUserClass(): void
    {
        $provider = new FrontendUserProvider($this->mockContaoFramework());

        $this->assertTrue($provider->supportsClass('Contao\FrontendUser'));
        $this->assertFalse($provider->supportsClass('Contao\BackendUser'));
    }
}
