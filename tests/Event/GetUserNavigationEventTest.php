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

        $this->event = new GetUserNavigationEvent([], false);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetUserNavigationEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals([], $this->event->getModules());
        $this->assertFalse($this->event->isShowAll());
    }

    /**
     * Tests the setModule() method.
     */
    public function testSetModules()
    {
        $this->event->setModules(['foo']);
        $this->assertEquals(['foo'], $this->event->getModules());
    }
}
