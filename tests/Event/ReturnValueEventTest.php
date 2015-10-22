<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ReturnValueEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ReturnValueEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReturnValueEventTest extends TestCase
{
    /**
     * @var ReturnValueEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new ReturnValueEvent('foo');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\ReturnValueEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getValue());

        $this->event->setValue('bar');

        $this->assertEquals('bar', $this->event->getValue());
    }
}
