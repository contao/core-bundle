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

        $this->event = new IsVisibleElementEvent(false, new LayoutModel());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\IsVisibleElementEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertFalse($this->event->isVisible());
        $this->assertInstanceOf('Contao\LayoutModel', $this->event->getElement());
    }

    /**
     * Tests the setVisible() method.
     */
    public function testSetVisible()
    {
        $this->event->setVisible(true);
        $this->assertTrue($this->event->isVisible());

        $this->event->setVisible(null);
        $this->assertFalse($this->event->isVisible());
    }
}
