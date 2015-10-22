<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetCombinedFileEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetCombinedFileEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCombinedFileEventTest extends TestCase
{
    /**
     * @var GetCombinedFileEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $content = 'foo';
        $key = 'bar';
        $mode = '.css';
        $file = ['name' => 'test.css'];

        $this->event = new GetCombinedFileEvent($content, $key, $mode, $file);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetCombinedFileEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getContent());
        $this->assertEquals('bar', $this->event->getKey());
        $this->assertEquals('.css', $this->event->getMode());
        $this->assertEquals(['name' => 'test.css'], $this->event->getFile());

        $this->event->setContent('foobar');
        $this->event->setKey('foo');
        $this->event->setMode('.js');
        $this->event->setFile(['name' => 'test.js']);

        $this->assertEquals('foobar', $this->event->getContent());
        $this->assertEquals('foo', $this->event->getKey());
        $this->assertEquals('.js', $this->event->getMode());
        $this->assertEquals(['name' => 'test.js'], $this->event->getFile());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $content = 'foo';
        $key = 'bar';
        $mode = '.css';
        $file = ['name' => 'test.css'];

        $this->event = new GetCombinedFileEvent($content, $key, $mode, $file);

        // Try to change the original variables
        $content = 'foobar';
        $key = 'foo';
        $mode = '.js';
        $file = ['name' => 'test.js'];

        $this->assertEquals('foo', $this->event->getContent());
        $this->assertEquals('foo', $this->event->getKey());
        $this->assertEquals('.js', $this->event->getMode());
        $this->assertEquals(['name' => 'test.js'], $this->event->getFile());
    }
}
