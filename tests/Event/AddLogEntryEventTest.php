<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\AddLogEntryEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the AddLogEntryEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddLogEntryEventTest extends TestCase
{
    /**
     * @var AddLogEntryEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $text     = 'foobar';
        $function = 'Foo::bar()';
        $category = 'test';

        $this->event = new AddLogEntryEvent($text, $function, $category);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\AddLogEntryEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foobar', $this->event->getText());
        $this->assertEquals('Foo::bar()', $this->event->getFunction());
        $this->assertEquals('test', $this->event->getCategory());

        $this->event->setText('bar');
        $this->event->setFunction('Bar::foo()');
        $this->event->setCategory('dev');

        $this->assertEquals('bar', $this->event->getText());
        $this->assertEquals('Bar::foo()', $this->event->getFunction());
        $this->assertEquals('dev', $this->event->getCategory());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $text     = 'foobar';
        $function = 'Foo::bar()';
        $category = 'test';

        $this->event = new AddLogEntryEvent($text, $function, $category);

        // Try to change the original variables
        $text     = 'bar';
        $function = 'Bar::foo()';
        $category = 'dev';

        $this->assertEquals('bar', $this->event->getText());
        $this->assertEquals('Bar::foo()', $this->event->getFunction());
        $this->assertEquals('dev', $this->event->getCategory());
    }
}
