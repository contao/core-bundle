<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetFrontendModuleEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\ModuleHtml;
use Contao\ModuleModel;

/**
 * Tests the GetFrontendModuleEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetFrontendModuleEventTest extends TestCase
{
    /**
     * @var GetFrontendModuleEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new GetFrontendModuleEvent('foo', [], new ModuleHtml(new ModuleModel()));
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetFrontendModuleEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\Module', $this->event->getModule());
    }

    /**
     * Tests the setBuffer() method.
     */
    public function testSetBuffer()
    {
        $this->event->setBuffer('bar');
        $this->assertEquals('bar', $this->event->getBuffer());
    }
}
