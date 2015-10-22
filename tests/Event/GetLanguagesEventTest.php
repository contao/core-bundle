<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetLanguagesEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetLanguagesEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetLanguagesEventTest extends TestCase
{
    /**
     * @var GetLanguagesEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $return = ['en' => 'English'];
        $languages = ['en'];
        $langsNative = ['en'];
        $installedOnly = false;

        $this->event = new GetLanguagesEvent($return, $languages, $langsNative, $installedOnly);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetLanguagesEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals(['en' => 'English'], $this->event->getReturn());
        $this->assertEquals(['en'], $this->event->getLanguages());
        $this->assertEquals(['en'], $this->event->getLangsNative());
        $this->assertFalse($this->event->getInstalledOnly());

        $this->event->setReturn(['de' => 'Deutsch']);
        $this->event->setLanguages(['de']);
        $this->event->setLangsNative(['de']);
        $this->event->setInstalledOnly(true);

        $this->assertEquals(['de' => 'Deutsch'], $this->event->getReturn());
        $this->assertEquals(['de'], $this->event->getLanguages());
        $this->assertEquals(['de'], $this->event->getLangsNative());
        $this->assertTrue($this->event->getInstalledOnly());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $return = ['en' => 'English'];
        $languages = ['en'];
        $langsNative = ['en'];
        $installedOnly = false;

        $this->event = new GetLanguagesEvent($return, $languages, $langsNative, $installedOnly);

        // Try to change the original variables
        $return = ['de' => 'Deutsch'];
        $languages = ['de'];
        $langsNative = ['de'];
        $installedOnly = true;

        $this->assertEquals(['de' => 'Deutsch'], $this->event->getReturn());
        $this->assertEquals(['de'], $this->event->getLanguages());
        $this->assertEquals(['de'], $this->event->getLangsNative());
        $this->assertTrue($this->event->getInstalledOnly());
    }
}
