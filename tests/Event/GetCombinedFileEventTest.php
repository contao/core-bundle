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

        $this->event = new GetCombinedFileEvent('foo', 'bar', '.css', ['name' => 'test.css']);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetCombinedFileEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getContent());
        $this->assertEquals('bar', $this->event->getKey());
        $this->assertEquals('.css', $this->event->getMode());
        $this->assertEquals(['name' => 'test.css'], $this->event->getFile());
    }

    /**
     * Tests the setContent() method.
     */
    public function testSetContent()
    {
        $this->event->setContent('foobar');
        $this->assertEquals('foobar', $this->event->getContent());
    }
}
