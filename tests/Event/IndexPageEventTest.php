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

        $this->event = new IndexPageEvent('foo', ['content' => 'foo'], ['url' => 'en/test.html']);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\IndexPageEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getContent());
        $this->assertEquals(['content' => 'foo'], $this->event->getData());
        $this->assertEquals(['url' => 'en/test.html'], $this->event->getSet());
    }

    /**
     * Tests the setContent() method.
     */
    public function testSetContent()
    {
        $this->event->setContent('foobar');
        $this->assertEquals('foobar', $this->event->getContent());
    }

    /**
     * Tests the setData() method.
     */
    public function testSetData()
    {
        $this->event->setData(['content' => 'foobar']);
        $this->assertEquals(['content' => 'foobar'], $this->event->getData());
    }

    /**
     * Tests the setSet() method.
     */
    public function testSetSet()
    {
        $this->event->setSet(['url' => 'de/test.html']);
        $this->assertEquals(['url' => 'de/test.html'], $this->event->getSet());
    }
}
