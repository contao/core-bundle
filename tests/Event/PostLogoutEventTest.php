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
    /**
     * @var PostLogoutEvent
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->event = new PostLogoutEvent($this->createMock(User::class));
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\PostLogoutEvent', $this->event);
    }

    public function testReturnsTheUserObject(): void
    {
        $this->assertInstanceOf('Contao\User', $this->event->getUser());
    }
}
