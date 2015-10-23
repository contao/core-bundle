<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ParseWidgetEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\TextField;

/**
 * Tests the ParseWidgetEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ParseWidgetEventTest extends TestCase
{
    /**
     * @var ParseWidgetEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new ParseWidgetEvent('foo', new TextField());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ParseWidgetEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertInstanceOf('Contao\TextField', $this->event->getWidget());
    }

    /**
     * Tests the setBuffer() method.
     */
    public function testSetBuffer()
    {
        $this->event->setBuffer('buffer');
        $this->assertEquals('buffer', $this->event->getBuffer());
    }
}
