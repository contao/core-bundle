<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\IndexPageEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the IndexPageEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class IndexPageEventTest extends TestCase
{
    /**
     * @var IndexPageEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $content = 'foo';
        $data = ['content' => 'foo'];
        $set = ['url' => 'en/test.html'];

        $this->event = new IndexPageEvent($content, $data, $set);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\IndexPageEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('foo', $this->event->getContent());
        $this->assertEquals(['content' => 'foo'], $this->event->getData());
        $this->assertEquals(['url' => 'en/test.html'], $this->event->getSet());

        $this->event->setContent('foobar');
        $this->event->setData(['content' => 'foobar']);
        $this->event->setSet(['url' => 'de/test.html']);

        $this->assertEquals('foobar', $this->event->getContent());
        $this->assertEquals(['content' => 'foobar'], $this->event->getData());
        $this->assertEquals(['url' => 'de/test.html'], $this->event->getSet());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $content = 'foo';
        $data = ['content' => 'foo'];
        $set = ['url' => 'en/test.html'];

        $this->event = new IndexPageEvent($content, $data, $set);

        // Try to change the original variables
        $content = 'bar';
        $data = ['content' => 'bar'];
        $set = ['url' => 'fr/test.html'];

        $this->assertEquals('bar', $this->event->getContent());
        $this->assertEquals(['content' => 'bar'], $this->event->getData());
        $this->assertEquals(['url' => 'fr/test.html'], $this->event->getSet());
    }
}
