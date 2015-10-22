<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GenerateFrontendUrlEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GenerateFrontendUrlEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GenerateFrontendUrlEventTest extends TestCase
{
    /**
     * @var GenerateFrontendUrlEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $url    = 'http://localhost';
        $row    = [];
        $params = '';

        $this->event = new GenerateFrontendUrlEvent($url, $row, $params);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GenerateFrontendUrlEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('http://localhost', $this->event->getUrl());
        $this->assertEquals([], $this->event->getRow());
        $this->assertEquals('', $this->event->getParams());

        $this->event->setUrl('http://127.0.0.1');
        $this->event->setRow(['foo']);
        $this->event->setParams('foo=bar');

        $this->assertEquals('http://127.0.0.1', $this->event->getUrl());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals('foo=bar', $this->event->getParams());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $url    = 'http://localhost';
        $row    = [];
        $params = [];

        $this->event = new GenerateFrontendUrlEvent($url, $row, $params);

        // Try to change the original variables
        $url    = 'http://127.0.0.1';
        $row    = ['foo'];
        $params = 'foo=bar';

        $this->assertEquals('http://localhost', $this->event->getUrl());
        $this->assertEquals(['foo'], $this->event->getRow());
        $this->assertEquals('foo=bar', $this->event->getParams());
    }
}
