<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\AddCustomRegexpEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\TextField;

/**
 * Tests the AddCustomRegexpEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddCustomRegexpEventTest extends TestCase
{
    /**
     * @var AddCustomRegexpEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new AddCustomRegexpEvent('email', 'test@example.com', new TextField());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\AddCustomRegexpEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('email', $this->event->getName());
        $this->assertEquals('test@example.com', $this->event->getInput());
        $this->assertInstanceOf('Contao\TextField', $this->event->getWidget());
    }
}
