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

        $this->event = new GetLanguagesEvent(
            ['de' => 'German - Deutsch'],
            ['de' => 'German'],
            ['de' => 'Deutsch'],
            false
        );
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetLanguagesEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals(['de' => 'German - Deutsch'], $this->event->getValue());
        $this->assertEquals(['de' => 'German'], $this->event->getLanguages());
        $this->assertEquals(['de' => 'Deutsch'], $this->event->getLangsNative());
        $this->assertFalse($this->event->isInstalledOnly());
    }

    /**
     * Tests the setReturnValue() method.
     */
    public function testSetReturnValue()
    {
        $this->event->setValue(['fr' => 'French - Français']);
        $this->assertEquals(['fr' => 'French - Français'], $this->event->getValue());
    }
}
