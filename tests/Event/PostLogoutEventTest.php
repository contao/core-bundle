<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Event;

use Contao\CoreBundle\Event\PostLogoutEvent;
use Contao\User;
use PHPUnit\Framework\TestCase;

class PostLogoutEventTest extends TestCase
{
    /** @var User */
    protected $user;

    /** @var PostLogoutEvent */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->user = $this->mockUser();
        $this->event = new PostLogoutEvent($this->user);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\PostLogoutEvent', $this->event);
        $this->assertSame('contao.postLogout', PostLogoutEvent::NAME);
    }

    /**
     * Tests the return of the user object.
     */
    public function testReturnsUser(): void
    {
        $this->assertInstanceOf('Contao\User', $this->event->getUser());
    }

    /**
     * Mocks the user.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockUser(): \PHPUnit_Framework_MockObject_MockObject
    {
        $user = $this->createPartialMock('Contao\User', []);

        return $user;
    }
}
