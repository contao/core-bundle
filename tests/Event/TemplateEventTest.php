<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\TemplateEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\FrontendTemplate;

/**
 * Tests the TemplateEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TemplateEventTest extends TestCase
{
    /**
     * @var TemplateEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new TemplateEvent('foo', 'bar', new FrontendTemplate());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\TemplateEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('foo', $this->event->getBuffer());
        $this->assertEquals('bar', $this->event->getKey());
        $this->assertInstanceOf('Contao\FrontendTemplate', $this->event->getTemplate());
    }

    /**
     * Tests the setBuffer() method.
     */
    public function testSetBuffer()
    {
        $this->event->setBuffer('buffer');
        $this->assertEquals('buffer', $this->event->getBuffer());
    }
}
