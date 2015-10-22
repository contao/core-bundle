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

        $this->event = new GetPageStatusIconEvent('foo.jpg', new PageModel());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetPageStatusIconEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo.jpg', $this->event->getImage());
        $this->assertInstanceOf('Contao\PageModel', $this->event->getPage());
    }

    /**
     * Tests the setImage() method.
     */
    public function testSetImage()
    {
        $this->event->setImage('bar.jpg');
        $this->assertEquals('bar.jpg', $this->event->getImage());
    }
}
