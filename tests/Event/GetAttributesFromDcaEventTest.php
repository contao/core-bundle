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

        $this->event = new GetAttributesFromDcaEvent([], new DC_Table('tl_user'));
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetAttributesFromDcaEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals([], $this->event->getAttributes());
        $this->assertInstanceOf('Contao\DC_Table', $this->event->getDataContainer());
    }

    /**
     * Tests the setAttributes() method.
     */
    public function testSetAttributes()
    {
        $this->event->setAttributes(['class' => 'w50']);
        $this->assertEquals(['class' => 'w50'], $this->event->getAttributes());
    }
}
