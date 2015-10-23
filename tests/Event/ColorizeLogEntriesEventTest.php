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

        $this->event = new ColorizeLogEntriesEvent('foo', ['ACTION' => 'CRON']);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ColorizeLogEntriesEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getLabel());
        $this->assertEquals(['ACTION' => 'CRON'], $this->event->getRow());
    }

    /**
     * Tests the setLabel() method.
     */
    public function testSetLabel()
    {
        $this->event->setLabel('bar');
        $this->assertEquals('bar', $this->event->getLabel());
    }
}
