<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\PageEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

/**
 * Tests the PageEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PageEventTest extends TestCase
{
    /**
     * @var PageEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $page    = new PageModel();
        $layout  = new LayoutModel();
        $handler = new PageRegular();

        $this->event = new PageEvent($page, $layout, $handler);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\PageEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertInstanceOf('Contao\\PageModel', $this->event->getPage());
        $this->assertInstanceOf('Contao\\LayoutModel', $this->event->getLayout());
        $this->assertInstanceOf('Contao\\PageRegular', $this->event->getHandler());

        $page    = new PageModel();
        $layout  = new LayoutModel();
        $handler = new PageRegular();

        $this->event->setPage($page);
        $this->event->setLayout($layout);
        $this->event->setHandler($handler);

        $this->assertEquals($page, $this->event->getPage());
        $this->assertEquals($layout, $this->event->getLayout());
        $this->assertEquals($handler, $this->event->getHandler());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $page     = new PageModel();
        $page2    = new PageModel();
        $layout   = new LayoutModel();
        $layout2  = new LayoutModel();
        $handler  = new PageRegular();
        $handler2 = new PageRegular();

        $this->event = new PageEvent($page, $layout, $handler);

        // Try to change the original variables
        $page    = $page2;
        $layout  = $layout2;
        $handler = $handler2;

        $this->assertEquals($page2, $this->event->getPage());
        $this->assertEquals($layout2, $this->event->getLayout());
        $this->assertEquals($handler2, $this->event->getHandler());
    }
}
