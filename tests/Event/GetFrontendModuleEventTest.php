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

        $buffer = 'foo';
        $row    = [];
        $module = new ModuleHtml(new ModuleModel());

        $this->event = new GetFrontendModuleEvent($buffer, $row, $module);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GetFrontendModuleEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\\Module', $this->event->getModule());

        $module = new ModuleHtml(new ModuleModel());

        $this->event->setBuffer('bar');
        $this->event->setRow(['foo']);
        $this->event->setModule($module);

        $this->assertEquals('bar', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($module, $this->event->getModule());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer  = 'foo';
        $row     = [];
        $module  = new ModuleHtml(new ModuleModel());
        $module2 = new ModuleHtml(new ModuleModel());

        $this->event = new GetFrontendModuleEvent($buffer, $row, $module);

        // Try to change the original variables
        $buffer = 'bar';
        $row    = ['foo'];
        $module = $module2;

        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($module2, $this->event->getModule());
    }
}
