<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetCountriesEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetCountriesEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCountriesEventTest extends TestCase
{
    /**
     * @var GetCountriesEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $return    = ['en' => 'English'];
        $countries = ['en'];

        $this->event = new GetCountriesEvent($return, $countries);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetCountriesEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals(['en' => 'English'], $this->event->getReturn());
        $this->assertEquals(['en'], $this->event->getCountries());

        $this->event->setReturn(['de' => 'Deutsch']);
        $this->event->setCountries(['de']);

        $this->assertEquals(['de' => 'Deutsch'], $this->event->getReturn());
        $this->assertEquals(['de'], $this->event->getCountries());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $return    = ['en' => 'English'];
        $countries = ['en'];

        $this->event = new GetCountriesEvent($return, $countries);

        // Try to change the original variables
        $return    = ['de' => 'Deutsch'];
        $countries = ['de'];

        $this->assertEquals(['de' => 'Deutsch'], $this->event->getReturn());
        $this->assertEquals(['de'], $this->event->getCountries());
    }
}
