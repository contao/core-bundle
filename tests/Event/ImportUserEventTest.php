<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ImportUserEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ImportUserEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ImportUserEventTest extends TestCase
{
    /**
     * @var ImportUserEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new ImportUserEvent('foo', 'bar', 'tl_user');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ImportUserEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getUsername());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertEquals('tl_user', $this->event->getTable());
        $this->assertFalse($this->event->isLoaded());
    }

    /**
     * Tests the setLoaded() method.
     */
    public function testSetLoaded()
    {
        $this->event->setLoaded(true);
        $this->assertTrue($this->event->isLoaded());

        $this->event->setLoaded(null);
        $this->assertFalse($this->event->isLoaded());
    }
}
