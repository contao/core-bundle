<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\CoreBundle\Event\TemplateEvent;
use Contao\CoreBundle\EventListener\Hook\ParseFrontendTemplateListener;
use Contao\CoreBundle\Test\TestCase;
use Contao\FrontendTemplate;

/**
 * Tests the ParseFrontendTemplateListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class ParseFrontendTemplateListenerTest extends TestCase
{
    /**
     * @var ParseFrontendTemplateListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new ParseFrontendTemplateListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\ParseFrontendTemplateListener', $this->listener);
    }

    /**
     * Tests the onParseFrontendTemplate() method.
     */
    public function testOnParseFrontendTemplate()
    {
        $buffer   = 'foo';
        $key      = 'bar';
        $template = new FrontendTemplate();
        $event    = new TemplateEvent($buffer, $key, $template);

        $GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = function ($buffer) {
            return $buffer;
        };

        $this->listener->onParseFrontendTemplate($event);

        $this->assertEquals($buffer, $event->getBuffer());
        $this->assertEquals($key, $event->getKey());
        $this->assertEquals($template, $event->getTemplate());

        unset($GLOBALS['TL_HOOKS']);
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $buffer    = 'foo';
        $key       = 'bar';
        $template  = new FrontendTemplate();
        $event     = new TemplateEvent($buffer, $key, $template);

        $template2 = new FrontendTemplate();

        $GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = function (
            $buffer,
            &$key,
            FrontendTemplate &$template
        ) use ($template2) {
            $key      = 'changed';
            $template = $template2;
        };

        $this->listener->onParseFrontendTemplate($event);

        $this->assertEquals('', $event->getBuffer());
        $this->assertEquals('changed', $event->getKey());
        $this->assertEquals($template2, $event->getTemplate());

        unset($GLOBALS['TL_HOOKS']);
    }
}
