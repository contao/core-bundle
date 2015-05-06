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

        $buffer = 'foo';
        $row    = [];
        $form   = new Form(new FormModel());

        $this->event = new GetFormEvent($buffer, $row, $form);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GetFormEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\\Form', $this->event->getForm());

        $form = new Form(new FormModel());

        $this->event->setBuffer('bar');
        $this->event->setRow(['foo']);
        $this->event->setForm($form);

        $this->assertEquals('bar', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($form, $this->event->getForm());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer = 'foo';
        $row    = [];
        $form   = new Form(new FormModel());
        $form2  = new Form(new FormModel());

        $this->event = new GetFormEvent($buffer, $row, $form);

        // Try to change the original variables
        $buffer = 'bar';
        $row    = ['foo'];
        $form   = $form2;

        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($form2, $this->event->getForm());
    }
}
