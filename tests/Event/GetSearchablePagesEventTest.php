<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetSearchablePagesEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetSearchablePagesEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetSearchablePagesEventTest extends TestCase
{
    /**
     * @var GetSearchablePagesEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new GetSearchablePagesEvent([], 1, 'en');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetSearchablePagesEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals([], $this->event->getPages());
        $this->assertEquals(1, $this->event->getRootId());
        $this->assertEquals('en', $this->event->getLanguage());
    }

    /**
     * Tests the setPages() method.
     */
    public function testSetPages()
    {
        $this->event->setPages([2]);
        $this->assertEquals([2], $this->event->getPages());
    }
}
