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

        $table        = 'foo';
        $newRecordIds = [];
        $parentTable  = 'bar';
        $childTables  = [];

        $this->event = new ReviseTableEvent($table, $newRecordIds, $parentTable, $childTables);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\ReviseTableEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertFalse($this->event->getReload());
        $this->assertEquals('foo', $this->event->getTable());
        $this->assertEquals([], $this->event->getNewRecordIds());
        $this->assertEquals('bar', $this->event->getParentTable());
        $this->assertEquals([], $this->event->getChildTables());

        $this->event->setReload(true);
        $this->event->setTable('test');
        $this->event->setNewRecordIds([2, 3]);
        $this->event->setParentTable('parent');
        $this->event->setChildTables(['child']);

        $this->assertTrue($this->event->getReload());
        $this->assertEquals('test', $this->event->getTable());
        $this->assertEquals([2, 3], $this->event->getNewRecordIds());
        $this->assertEquals('parent', $this->event->getParentTable());
        $this->assertEquals(['child'], $this->event->getChildTables());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $table        = 'foo';
        $newRecordIds = [2, 3];
        $parentTable  = 'bar';
        $childTables  = [];

        $this->event = new ReviseTableEvent($table, $newRecordIds, $parentTable, $childTables);

        // Try to change the original variables
        $table        = 'bar';
        $newRecordIds = [4];
        $parentTable  = 'parent';
        $childTables  = ['child'];

        $this->assertEquals('bar', $this->event->getTable());
        $this->assertEquals([4], $this->event->getNewRecordIds());
        $this->assertEquals('parent', $this->event->getParentTable());
        $this->assertEquals(['child'], $this->event->getChildTables());
    }
}
