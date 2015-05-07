<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetUserNavigationEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\Form;
use Contao\FormModel;

/**
 * Tests the GetUserNavigationEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetUserNavigationEventTest extends TestCase
{
    /**
     * @var GetUserNavigationEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $modules = [];
        $showAll = false;

        $this->event = new GetUserNavigationEvent($modules, $showAll);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GetUserNavigationEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals([], $this->event->getModules());
        $this->assertFalse($this->event->getShowAll());

        $this->event->setModules(['foo']);
        $this->event->setShowAll(true);

        $this->assertEquals(['foo'], $this->event->getModules());
        $this->assertTrue($this->event->getShowAll());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $modules = [];
        $showAll = false;

        $this->event = new GetUserNavigationEvent($modules, $showAll);

        // Try to change the original variables
        $modules = ['foo'];
        $showAll = true;

        $this->assertEquals([], $this->event->getModules());
        $this->assertTrue($this->event->getShowAll());
    }
}
