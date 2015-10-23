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

        $this->event = new PageEvent(new PageModel(), new LayoutModel(), new PageRegular());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\PageEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertInstanceOf('Contao\PageModel', $this->event->getPage());
        $this->assertInstanceOf('Contao\LayoutModel', $this->event->getLayout());
        $this->assertInstanceOf('Contao\PageRegular', $this->event->getHandler());
    }
}
