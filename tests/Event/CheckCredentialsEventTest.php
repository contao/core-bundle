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

        $username = 'foo';
        $password = 'bar';
        $user = BackendUser::getInstance();

        $this->event = new CheckCredentialsEvent($username, $password, $user);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\CheckCredentialsEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertFalse($this->event->getAuthenticated());
        $this->assertEquals('foo', $this->event->getUsername());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertInstanceOf('Contao\BackendUser', $this->event->getUser());

        $user = BackendUser::getInstance();

        $this->event->setAuthenticated(true);
        $this->event->setUsername('bar');
        $this->event->setPassword('foo');
        $this->event->setUser($user);

        $this->assertTrue($this->event->getAuthenticated());
        $this->assertEquals('bar', $this->event->getUsername());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertEquals($user, $this->event->getUser());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $username = 'foo';
        $password = 'bar';
        $user = BackendUser::getInstance();
        $user2 = BackendUser::getInstance();

        $this->event = new CheckCredentialsEvent($username, $password, $user);

        // Try to change the original variables
        $username = 'bar';
        $password = 'foo';
        $user = $user2;

        $this->assertEquals('bar', $this->event->getUsername());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertEquals($user2, $this->event->getUser());
    }
}
