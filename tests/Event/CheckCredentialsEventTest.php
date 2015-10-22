<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\BackendUser;
use Contao\CoreBundle\Event\CheckCredentialsEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the CheckCredentialsEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class CheckCredentialsEventTest extends TestCase
{
    /**
     * @var CheckCredentialsEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new CheckCredentialsEvent('foo', 'bar', BackendUser::getInstance());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\CheckCredentialsEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getUsername());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertInstanceOf('Contao\BackendUser', $this->event->getUser());
        $this->assertFalse($this->event->isAuthenticated());
    }

    /**
     * Tests the setAuthenticated() method.
     */
    public function testSetAuthenticated()
    {
        $this->event->setAuthenticated(true);
        $this->assertTrue($this->event->isAuthenticated());

        $this->event->setAuthenticated(null);
        $this->assertFalse($this->event->isAuthenticated());
    }
}
