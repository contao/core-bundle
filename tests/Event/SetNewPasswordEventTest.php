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

        $this->event = new SetNewPasswordEvent(new MemberModel(), 'foo', new ModuleHtml(new ModuleModel()));
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\SetNewPasswordEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertInstanceOf('Contao\MemberModel', $this->event->getMember());
        $this->assertEquals('foo', $this->event->getPassword());
        $this->assertInstanceOf('Contao\ModuleHtml', $this->event->getModule());
    }
}
