<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\AddCustomRegexpEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\TextField;

/**
 * Tests the AddCustomRegexpEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddCustomRegexpEventTest extends TestCase
{
    /**
     * @var AddCustomRegexpEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $rgxp   = 'email';
        $input  = 'test@example.com';
        $widget = new TextField();

        $this->event = new AddCustomRegexpEvent($rgxp, $input, $widget);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\AddCustomRegexpEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertNull($this->event->getBreak());
        $this->assertEquals('email', $this->event->getRgxp());
        $this->assertEquals('test@example.com', $this->event->getInput());
        $this->assertInstanceOf('Contao\TextField', $this->event->getWidget());

        $widget = new TextField();

        $this->event->setBreak(true);
        $this->event->setRgxp('url');
        $this->event->setInput('http://localhost');
        $this->event->setWidget($widget);

        $this->assertTrue($this->event->getBreak());
        $this->assertEquals('url', $this->event->getRgxp());
        $this->assertEquals('http://localhost', $this->event->getInput());
        $this->assertEquals($widget, $this->event->getWidget());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $rgxp    = 'email';
        $input   = 'test@example.com';
        $widget  = new TextField();
        $widget2 = new TextField();

        $this->event = new AddCustomRegexpEvent($rgxp, $input, $widget);

        // Try to change the original variables
        $rgxp    = 'url';
        $input = 'http://localhost';

        $this->assertEquals('url', $this->event->getRgxp());
        $this->assertEquals('http://localhost', $this->event->getInput());
        $this->assertEquals($widget2, $this->event->getWidget());
    }
}
