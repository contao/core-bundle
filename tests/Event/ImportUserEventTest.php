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

        $username = 'foo';
        $password = 'bar';
        $table    = 'tl_user';

        $this->event = new ImportUserEvent($username, $password, $table);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ImportUserEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertFalse($this->event->getLoaded());
        $this->assertEquals('foo', $this->event->getUsername());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertEquals('tl_user', $this->event->getTable());

        $this->event->setLoaded(true);
        $this->event->setUsername('bar');
        $this->event->setPassword('foo');
        $this->event->setTable('tl_member');

        $this->assertTrue($this->event->getLoaded());
        $this->assertEquals('bar', $this->event->getUsername());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertEquals('tl_member', $this->event->getTable());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $username = 'foo';
        $password = 'bar';
        $table    = 'tl_user';

        $this->event = new ImportUserEvent($username, $password, $table);

        // Try to change the original variables
        $username = 'bar';
        $password = 'foo';
        $table    = 'tl_member';

        $this->assertEquals('bar', $this->event->getUsername());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertEquals('tl_member', $this->event->getTable());
    }
}
