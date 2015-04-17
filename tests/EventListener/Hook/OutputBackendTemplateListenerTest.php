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
use Contao\CoreBundle\EventListener\Hook\OutputBackendTemplateListener;
use Contao\CoreBundle\Test\TestCase;
use Contao\FrontendTemplate;

/**
 * Tests the OutputBackendTemplateListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class OutputBackendTemplateListenerTest extends TestCase
{
    /**
     * @var OutputBackendTemplateListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new OutputBackendTemplateListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\OutputBackendTemplateListener', $this->listener);
    }

    /**
     * Tests the onOutputBackendTemplate() method.
     */
    public function testOnOutputBackendTemplate()
    {
        $buffer   = 'foo';
        $key      = 'bar';
        $template = new FrontendTemplate();
        $event    = new TemplateEvent($buffer, $key, $template);

        $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = function ($buffer) {
            return $buffer;
        };

        $this->listener->onOutputBackendTemplate($event);

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

        $GLOBALS['TL_HOOKS']['outputBackendTemplate'][] = function (
            $buffer,
            &$key,
            FrontendTemplate &$template
        ) use ($template2) {
            $key      = 'changed';
            $template = $template2;
        };

        $this->listener->onOutputBackendTemplate($event);

        $this->assertEquals('', $event->getBuffer());
        $this->assertEquals('changed', $event->getKey());
        $this->assertEquals($template2, $event->getTemplate());

        unset($GLOBALS['TL_HOOKS']);
    }
}
