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
use Contao\FrontendTemplate;
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

        $buffer = 'foo';
        $widget = new TextField();

        $this->event = new ParseWidgetEvent($buffer, $widget);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\ParseWidgetEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertInstanceOf('Contao\\TextField', $this->event->getWidget());

        $widget = new TextField();

        $this->event->setBuffer('buffer');
        $this->event->setWidget($widget);

        $this->assertEquals('buffer', $this->event->getBuffer());
        $this->assertEquals($widget, $this->event->getWidget());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer  = 'foo';
        $widget  = new TextField();
        $widget2 = new TextField();

        $this->event = new ParseWidgetEvent($buffer, $widget);

        // Try to change the original variables
        $buffer = 'buffer';
        $widget = $widget2;

        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals($widget2, $this->event->getWidget());
    }
}
