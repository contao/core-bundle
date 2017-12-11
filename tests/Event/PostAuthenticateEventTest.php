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
     * @var PostAuthenticateEvent
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->event = new PostAuthenticateEvent($this->createMock(User::class));
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\PostAuthenticateEvent', $this->event);
    }

    public function testReturnsTheUserObject(): void
    {
        $this->assertInstanceOf('Contao\User', $this->event->getUser());
    }
}
