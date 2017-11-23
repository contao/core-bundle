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

use Contao\CoreBundle\Event\ImportUserEvent;
use Contao\User;
use PHPUnit\Framework\TestCase;

class ImportUserEventTest extends TestCase
{
    /** @var User */
    protected $user;

    /** @var ImportUserEvent */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->user = $this->mockUser();
        $this->event = new ImportUserEvent('username', 'password', 'tl_user');
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ImportUserEvent', $this->event);
        $this->assertSame('contao.importUser', ImportUserEvent::NAME);
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
     * Tests the return of the table.
     */
    public function testReturnsTable(): void
    {
        $this->assertSame('tl_user', $this->event->getTable());
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
     * Tests the instantiation with possible null values.
     */
    public function testWithEmptyCredentialsAndEmptyTable(): void
    {
        $this->event = new ImportUserEvent('username', null, null);

        $this->assertSame('username', $this->event->getUsername());
        $this->assertNull($this->event->getCredentials());
        $this->assertNull($this->event->getTable());
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
