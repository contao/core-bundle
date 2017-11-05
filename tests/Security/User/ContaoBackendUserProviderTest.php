<?php

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
 * Tests the ContaoBackendUserProvider class.
 */
class ContaoBackendUserProviderTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $provider = new BackendUserProvider($this->mockContaoFramework());

        $this->assertInstanceOf('Contao\CoreBundle\Security\User\BackendUserProvider', $provider);
    }

    /**
     * Tests loading a backend user.
     */
    public function testLoadUserByUsername()
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
            ->expects($this->exactly(2))
            ->method('loadUserByUsername')
            ->with('test-user')
            ->willReturn($user)
        ;

        $framework = $this->mockContaoFramework(null, null, [BackendUser::class => $adapter]);
        $provider = new BackendUserProvider($framework);

        $this->assertInstanceOf(BackendUser::class, $provider->loadUserByUsername('test-user'));
        $this->assertInstanceOf(BackendUser::class, $provider->refreshUser($user));
    }

    /**
     * Tests loading a backend user.
     */
    public function testUsernameNotFoundException()
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

        $framework = $this->mockContaoFramework(null, null, [BackendUser::class => $adapter]);

        $provider = new BackendUserProvider($framework);
        $provider->loadUserByUsername('test-user');
    }

    public function testUnsupportedUserException()
    {
        $provider = new BackendUserProvider($this->mockContaoFramework());
        $provider->refreshUser($this->getMock(UserInterface::class));
    }
}
