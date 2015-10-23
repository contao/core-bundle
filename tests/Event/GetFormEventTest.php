<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetFormEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\Form;
use Contao\FormModel;

/**
 * Tests the GetFormEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetFormEventTest extends TestCase
{
    /**
     * @var GetFormEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new GetFormEvent('foo', [], new Form(new FormModel()));
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetFormEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\Form', $this->event->getForm());
    }

    /**
     * Tests the setBuffer() method.
     */
    public function testSetBuffer()
    {
        $this->event->setBuffer('bar');
        $this->assertEquals('bar', $this->event->getBuffer());
    }
}
