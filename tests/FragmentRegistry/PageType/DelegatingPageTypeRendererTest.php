<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\PageType;

use Contao\CoreBundle\FragmentRegistry\PageType\DelegatingPageTypeRenderer;
use Contao\CoreBundle\FragmentRegistry\PageType\PageTypeRendererInterface;
use Contao\CoreBundle\Tests\TestCase;
use Contao\PageModel;

/**
 * Class DelegatingPageTypeRendererTest.
 *
 * @author Yanick Witschi
 */
class DelegatingPageTypeRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $renderer = new DelegatingPageTypeRenderer([]);

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\PageType\DelegatingPageTypeRenderer', $renderer);
    }

    public function testReturnsTrueIfOneOfTheRenderersSupportsTheModel()
    {
        $renderer1 = $this->createMock(PageTypeRendererInterface::class);

        $renderer1
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true)
        ;

        $renderer2 = $this->createMock(PageTypeRendererInterface::class);

        $renderer2
            ->expects($this->never())
            ->method('supports')
        ;

        $renderer = new DelegatingPageTypeRenderer([$renderer1, $renderer2]);

        $this->assertTrue($renderer->supports(new PageModel()));
    }

    public function testReturnsFalseIfNoneOfTheRenderersSupportsTheModel()
    {
        $renderer1 = $this->createMock(PageTypeRendererInterface::class);

        $renderer1
            ->expects($this->once())
            ->method('supports')
            ->willReturn(false)
        ;

        $renderer2 = $this->createMock(PageTypeRendererInterface::class);

        $renderer2
            ->expects($this->once())
            ->method('supports')
            ->willReturn(false)
        ;

        $renderer = new DelegatingPageTypeRenderer([$renderer1, $renderer2]);

        $this->assertFalse($renderer->supports(new PageModel()));
    }

    public function testRendersTheFragmentIfOneOfTheRenderersSupportsTheModel()
    {
        $renderer1 = $this->createMock(PageTypeRendererInterface::class);

        $renderer1
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true)
        ;

        $renderer1
            ->expects($this->once())
            ->method('render')
            ->willReturn('foobar')
        ;

        $renderer2 = $this->createMock(PageTypeRendererInterface::class);

        $renderer2
            ->expects($this->never())
            ->method('supports')
        ;

        $renderer2
            ->expects($this->never())
            ->method('render')
        ;

        $renderer = new DelegatingPageTypeRenderer([$renderer1, $renderer2]);

        $this->assertSame('foobar', $renderer->render(new PageModel()));
    }
}
