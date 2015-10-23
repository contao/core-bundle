<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GenerateFrontendUrlEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GenerateFrontendUrlEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GenerateFrontendUrlEventTest extends TestCase
{
    /**
     * @var GenerateFrontendUrlEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new GenerateFrontendUrlEvent('http://localhost', [], '');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GenerateFrontendUrlEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('http://localhost', $this->event->getUrl());
        $this->assertEquals([], $this->event->getRow());
        $this->assertEquals('', $this->event->getParams());
    }

    /**
     * Tests the setUrl() method.
     */
    public function testSetUrl()
    {
        $this->event->setUrl('http://127.0.0.1');
        $this->assertEquals('http://127.0.0.1', $this->event->getUrl());
    }
}
