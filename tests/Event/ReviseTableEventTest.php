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
        $this->event->setStatus(true);
        $this->event->setTable('test');
        $this->event->setNewRecordIds([2, 3]);
        $this->event->setParentTable('parent');
        $this->event->setChildTables(['child']);

        $this->assertTrue($this->event->getStatus());
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

        $event = new ReviseTableEvent($table, $newRecordIds, $parentTable, $childTables);

        // Change the original variables
        $table        = 'bar';
        $newRecordIds = [4];
        $parentTable  = 'parent';
        $childTables  = ['child'];

        $this->assertEquals('bar', $event->getTable());
        $this->assertEquals([4], $event->getNewRecordIds());
        $this->assertEquals('parent', $event->getParentTable());
        $this->assertEquals(['child'], $event->getChildTables());
    }
}
