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

use Contao\CoreBundle\Event\CheckCredentialsEvent;
use Contao\User;
use PHPUnit\Framework\TestCase;

class CheckCredentialsEventTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var CheckCredentialsEvent
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->user = $this->mockUser();
        $this->event = new CheckCredentialsEvent('username', 'password', $this->user);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\CheckCredentialsEvent', $this->event);
        $this->assertSame('contao.check_credentials', CheckCredentialsEvent::NAME);
    }

    /**
     * Tests the return of the username.
     */
    public function testReturnsUsername(): void
    {
        $this->assertSame('username', $this->event->getUsername());
    }

    /**
     * Tests the return of the credentials.
     */
    public function testReturnsCredentials(): void
    {
        $this->assertSame('password', $this->event->getCredentials());
    }

    /**
     * Tests the return of the user object.
     */
    public function testReturnsUser(): void
    {
        $this->assertInstanceOf('Contao\User', $this->event->getUser());
    }

    /**
     * Tests, if the return value is false when never voted.
     */
    public function testReturnFalseWhenNeverVoted(): void
    {
        $this->assertFalse($this->event->getVote());
    }

    /**
     * Tests, if the return value is true when at least one vote is positive.
     */
    public function testReturnsTrueWithAtLeastOnePositiveVote(): void
    {
        $this->event->vote(true);
        $this->assertTrue($this->event->getVote());
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
