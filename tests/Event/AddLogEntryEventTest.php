<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\AddLogEntryEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the AddLogEntryEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddLogEntryEventTest extends TestCase
{
    /**
     * @var AddLogEntryEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new AddLogEntryEvent('foobar', 'Foo::bar()', 'test');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\AddLogEntryEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foobar', $this->event->getText());
        $this->assertEquals('Foo::bar()', $this->event->getFunction());
        $this->assertEquals('test', $this->event->getCategory());
    }
}
