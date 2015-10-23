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

        $this->event = new GetContentElementEvent('foo', [], new ContentText(new ContentModel()));
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetContentElementEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals([], $this->event->getRow());
        $this->assertInstanceOf('Contao\ContentElement', $this->event->getElement());
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
