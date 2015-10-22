<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ParseDateEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ParseDateEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ParseDateEventTest extends TestCase
{
    /**
     * @var ParseDateEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $return = '2015-05-07';
        $format = 'Y-m-d';
        $timestamp = 1430985610;

        $this->event = new ParseDateEvent($return, $format, $timestamp);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ParseDateEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('2015-05-07', $this->event->getReturn());
        $this->assertEquals('Y-m-d', $this->event->getFormat());
        $this->assertEquals(1430985610, $this->event->getTimestamp());

        $this->event->setReturn('07.05.2015');
        $this->event->setFormat('d.m.Y');
        $this->event->setTimestamp(1399449692);

        $this->assertEquals('07.05.2015', $this->event->getReturn());
        $this->assertEquals('d.m.Y', $this->event->getFormat());
        $this->assertEquals(1399449692, $this->event->getTimestamp());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $return = '2015-05-07';
        $format = 'Y-m-d';
        $timestamp = 1430985610;

        $this->event = new ParseDateEvent($return, $format, $timestamp);

        // Try to change the original variables
        $return = '07.05.2015';
        $format = 'd.m.Y';
        $timestamp = 1399449692;

        $this->assertEquals('2015-05-07', $this->event->getReturn());
        $this->assertEquals('d.m.Y', $this->event->getFormat());
        $this->assertEquals(1399449692, $this->event->getTimestamp());
    }
}
