<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ColorizeLogEntriesEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ColorizeLogEntriesEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ColorizeLogEntriesEventTest extends TestCase
{
    /**
     * @var ColorizeLogEntriesEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $label = 'foo';
        $row   = ['ACTION' => 'CRON'];

        $this->event = new ColorizeLogEntriesEvent($label, $row);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ColorizeLogEntriesEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getLabel());
        $this->assertEquals(['ACTION' => 'CRON'], $this->event->getRow());

        $this->event->setLabel('bar');
        $this->event->setRow(['ACTION' => 'ERROR']);

        $this->assertEquals('bar', $this->event->getLabel());
        $this->assertEquals(['ACTION' => 'ERROR'], $this->event->getRow());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $label = 'foo';
        $row   = ['ACTION' => 'CRON'];

        $this->event = new ColorizeLogEntriesEvent($label, $row);

        // Try to change the original variables
        $label = 'bar';
        $row   = ['ACTION' => 'ERROR'];

        $this->assertEquals('foo', $this->event->getLabel());
        $this->assertEquals(['ACTION' => 'ERROR'], $this->event->getRow());
    }
}
