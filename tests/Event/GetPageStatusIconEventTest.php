<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetPageStatusIconEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\PageModel;

/**
 * Tests the GetPageStatusIconEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetPageStatusIconEventTest extends TestCase
{
    /**
     * @var GetPageStatusIconEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $image = 'foo.jpg';
        $page = new PageModel();

        $this->event = new GetPageStatusIconEvent($image, $page);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetPageStatusIconEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo.jpg', $this->event->getImage());
        $this->assertInstanceOf('Contao\PageModel', $this->event->getPage());

        $page = new PageModel();

        $this->event->setImage('bar.jpg');
        $this->event->setPage($page);

        $this->assertEquals('bar.jpg', $this->event->getImage());
        $this->assertEquals($page, $this->event->getPage());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $image = 'foo.jpg';
        $page = new PageModel();
        $page2 = new PageModel();

        $this->event = new GetPageStatusIconEvent($image, $page);

        // Try to change the original variables
        $image = 'bar.jpg';
        $page = $page2;

        $this->assertEquals('foo.jpg', $this->event->getImage());
        $this->assertEquals($page2, $this->event->getPage());
    }
}
