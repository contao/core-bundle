<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetAttributesFromDcaEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\DC_Table;

/**
 * Tests the GetAttributesFromDcaEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetAttributesFromDcaEventTest extends TestCase
{
    /**
     * @var GetAttributesFromDcaEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $attributes = [];
        $dca = new DC_Table('tl_user');

        $this->event = new GetAttributesFromDcaEvent($attributes, $dca);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetAttributesFromDcaEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals([], $this->event->getAttributes());
        $this->assertInstanceOf('Contao\DC_Table', $this->event->getDataContainer());

        $dca = new DC_Table('tl_member');

        $this->event->setAttributes(['class' => 'w50']);
        $this->event->setDataContainer($dca);

        $this->assertEquals(['class' => 'w50'], $this->event->getAttributes());
        $this->assertEquals($dca, $this->event->getDataContainer());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $attributes = [];
        $dca = new DC_Table('tl_user');
        $dca2 = new DC_Table('tl_member');

        $this->event = new GetAttributesFromDcaEvent($attributes, $dca);

        // Try to change the original variables
        $attributes = ['class' => 'w50'];
        $dca = $dca2;

        $this->assertEquals([], $this->event->getAttributes());
        $this->assertEquals($dca, $this->event->getDataContainer());
    }
}
