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

        $buffer   = 'foo';
        $key      = 'bar';
        $template = new FrontendTemplate();

        $this->event = new TemplateEvent($buffer, $key, $template);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\TemplateEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $template = new FrontendTemplate();

        $this->event->setBuffer('buffer');
        $this->event->setKey('key');
        $this->event->setTemplate($template);

        $this->assertEquals('buffer', $this->event->getBuffer());
        $this->assertEquals('key', $this->event->getKey());
        $this->assertEquals($template, $this->event->getTemplate());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer    = 'buffer';
        $key       = 'key';
        $template  = new FrontendTemplate();
        $template2 = new FrontendTemplate();
        $event     = new TemplateEvent($buffer, $key, $template);

        // Try to change the original variables
        $buffer   = 'foo';
        $key      = 'moo';
        $template = $template2;

        $this->assertEquals('buffer', $event->getBuffer());
        $this->assertEquals('moo', $event->getKey());
        $this->assertEquals($template2, $event->getTemplate());
    }
}
