<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ReviseTableEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ReviseTableEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReviseTableEventTest extends TestCase
{
    /**
     * @var ReviseTableEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new ReviseTableEvent('foo', [], 'bar', []);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ReviseTableEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getTable());
        $this->assertEquals([], $this->event->getNewRecordIds());
        $this->assertEquals('bar', $this->event->getParentTable());
        $this->assertEquals([], $this->event->getChildTables());
        $this->assertFalse($this->event->isReload());
    }

    /**
     * Tests the setReload() method.
     */
    public function testSetReload()
    {
        $this->event->setReload(true);
        $this->assertTrue($this->event->isReload());

        $this->event->setReload(null);
        $this->assertFalse($this->event->isReload());
    }
}
