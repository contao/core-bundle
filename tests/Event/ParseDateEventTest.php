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

        $this->event = new ParseDateEvent('2015-05-07', 'Y-m-d', 1430985610);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ParseDateEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('2015-05-07', $this->event->getValue());
        $this->assertEquals('Y-m-d', $this->event->getFormat());
        $this->assertEquals(1430985610, $this->event->getTimestamp());
    }

    /**
     * Tests the setValue() method.
     */
    public function testSetValue()
    {
        $this->event->setValue('07.05.2015');
        $this->assertEquals('07.05.2015', $this->event->getValue());
    }
}
