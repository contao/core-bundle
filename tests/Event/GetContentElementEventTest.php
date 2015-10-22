<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\ContentModel;
use Contao\ContentText;
use Contao\CoreBundle\Event\GetContentElementEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetContentElementEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetContentElementEventTest extends TestCase
{
    /**
     * @var GetContentElementEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $buffer = 'foo';
        $row = [];
        $element = new ContentText(new ContentModel());

        $this->event = new GetContentElementEvent($buffer, $row, $element);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetContentElementEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\ContentElement', $this->event->getElement());

        $element = new ContentText(new ContentModel());

        $this->event->setBuffer('bar');
        $this->event->setRow(['foo']);
        $this->event->setElement($element);

        $this->assertEquals('bar', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($element, $this->event->getElement());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer = 'foo';
        $row = [];
        $element = new ContentText(new ContentModel());
        $element2 = new ContentText(new ContentModel());

        $this->event = new GetContentElementEvent($buffer, $row, $element);

        // Try to change the original variables
        $buffer = 'bar';
        $row = ['foo'];
        $element = $element2;

        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals($element, $this->event->getElement());
    }
}
