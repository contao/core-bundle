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
use PHPUnit\Framework\TestCase;

class ImportUserEventTest extends TestCase
{
    /**
     * @var ImportUserEvent
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->event = new ImportUserEvent('username', 'password', 'tl_user');
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ImportUserEvent', $this->event);
    }

    public function testReturnsTheUsername(): void
    {
        $this->assertSame('username', $this->event->getUsername());
    }

    public function testReturnsTheCredentials(): void
    {
        $this->assertSame('password', $this->event->getCredentials());
    }

    public function testReturnsTheTableName(): void
    {
        $this->assertSame('tl_user', $this->event->getTable());
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
