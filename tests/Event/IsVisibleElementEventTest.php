<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\LayoutModel;
use Contao\CoreBundle\Event\IsVisibleElementEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the IsVisibleElementEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class IsVisibleElementEventTest extends TestCase
{
    /**
     * @var IsVisibleElementEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $status = false;
        $model  = new LayoutModel();

        $this->event = new IsVisibleElementEvent($status, $model);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\IsVisibleElementEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertFalse($this->event->getReturn());
        $this->assertInstanceOf('Contao\LayoutModel', $this->event->getElement());

        $model = new LayoutModel();

        $this->event->setReturn(true);
        $this->event->setElement($model);

        $this->assertTrue($this->event->getReturn());
        $this->assertEquals($model, $this->event->getElement());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $return = false;
        $model  = new LayoutModel();
        $model2 = new LayoutModel();

        $this->event = new IsVisibleElementEvent($return, $model);

        // Try to change the original variables
        $return = true;
        $model  = $model2;

        $this->assertFalse($this->event->getReturn());
        $this->assertEquals($model2, $this->event->getElement());
    }
}
