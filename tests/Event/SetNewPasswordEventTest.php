<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\SetNewPasswordEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\MemberModel;
use Contao\ModuleHtml;
use Contao\ModuleModel;

/**
 * Tests the SetNewPasswordEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SetNewPasswordEventTest extends TestCase
{
    /**
     * @var SetNewPasswordEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $member   = new MemberModel();
        $password = 'foo';
        $module   = new ModuleHtml(new ModuleModel());

        $this->event = new SetNewPasswordEvent($member, $password, $module);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\SetNewPasswordEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertInstanceOf('Contao\MemberModel', $this->event->getMember());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertInstanceOf('Contao\ModuleHtml', $this->event->getModule());

        $member = new MemberModel();
        $module = new ModuleHtml(new ModuleModel());

        $this->event->setMember($member);
        $this->event->setPassword('bar');
        $this->event->setModule($module);

        $this->assertEquals($member, $this->event->getMember());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertEquals($module, $this->event->getModule());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $member   = new MemberModel();
        $member2  = new MemberModel();
        $password = 'foo';
        $module   = new ModuleHtml(new ModuleModel());
        $module2  = new ModuleHtml(new ModuleModel());

        $this->event = new SetNewPasswordEvent($member, $password, $module);

        // Try to change the original variables
        $member   = $member2;
        $password = 'bar';
        $module   = $module2;

        $this->assertEquals($member2, $this->event->getMember());
        $this->assertEquals('bar', $this->event->getPassword());
        $this->assertEquals($module2, $this->event->getModule());
    }
}
