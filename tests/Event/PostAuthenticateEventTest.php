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

use Contao\CoreBundle\Event\PostAuthenticateEvent;
use Contao\User;
use PHPUnit\Framework\TestCase;

class PostAuthenticateEventTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var PostAuthenticateEvent
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->user = $this->mockUser();
        $this->event = new PostAuthenticateEvent($this->user);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\PostAuthenticateEvent', $this->event);
        $this->assertSame('contao.postAuthenticate', PostAuthenticateEvent::NAME);
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
     * @return User
     */
    private function mockUser(): User
    {
        /** @var User $user */
        $user = $this->createPartialMock('Contao\User', []);

        return $user;
    }
}
