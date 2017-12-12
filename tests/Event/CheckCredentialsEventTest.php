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
     * @var CheckCredentialsEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $user = $this->createMock(User::class);
        $this->event = new CheckCredentialsEvent('username', 'password', $user);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\CheckCredentialsEvent', $this->event);
    }

    public function testReturnsTheUsername(): void
    {
        $this->assertSame('username', $this->event->getUsername());
    }

    public function testReturnsTheCredentials(): void
    {
        $this->assertSame('password', $this->event->getCredentials());
    }

    public function testReturnsTheUserObject(): void
    {
        $this->assertInstanceOf('Contao\User', $this->event->getUser());
    }

    public function testReturnsFalseWhenNeverVoted(): void
    {
        $this->assertFalse($this->event->getVote());
    }

    public function testReturnsTrueIfThereIsAtLeastOnePositiveVote(): void
    {
        $this->event->vote(true);

        $this->assertTrue($this->event->getVote());
    }
}
