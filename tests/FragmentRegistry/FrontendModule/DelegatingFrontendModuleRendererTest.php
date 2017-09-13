<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\FrontendModule;

use Contao\CoreBundle\FragmentRegistry\FrontendModule\DelegatingFrontendModuleRenderer;
use Contao\CoreBundle\FragmentRegistry\FrontendModule\FrontendModuleRendererInterface;
use Contao\CoreBundle\Tests\TestCase;
use Contao\ModuleModel;

/**
 * Class DelegatingFrontendModuleRendererTest.
 *
 * @author Yanick Witschi
 */
class DelegatingFrontendModuleRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $renderer = new DelegatingFrontendModuleRenderer([]);

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\FrontendModule\DelegatingFrontendModuleRenderer', $renderer);
    }

    public function testSupports()
    {
        $renderer1 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(true);

        $renderer2 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer2->expects($this->never())->method('supports');

        $renderer = new DelegatingFrontendModuleRenderer([$renderer1, $renderer2]);
        $this->assertTrue($renderer->supports(new ModuleModel()));

        $renderer1 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(false);

        $renderer2 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer2->expects($this->once())->method('supports')->willReturn(false);

        $renderer = new DelegatingFrontendModuleRenderer([$renderer1, $renderer2]);
        $this->assertFalse($renderer->supports(new ModuleModel()));
    }

    public function testRender()
    {
        $renderer1 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(true);
        $renderer1->expects($this->once())->method('render')->willReturn('foobar');

        $renderer2 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer2->expects($this->never())->method('supports');
        $renderer2->expects($this->never())->method('render');

        $renderer = new DelegatingFrontendModuleRenderer([$renderer1, $renderer2]);
        $this->assertSame('foobar', $renderer->render(new ModuleModel()));

        $renderer1 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(false);

        $renderer2 = $this->createMock(FrontendModuleRendererInterface::class);
        $renderer2->expects($this->once())->method('supports')->willReturn(false);

        $renderer = new DelegatingFrontendModuleRenderer([$renderer1, $renderer2]);
        $this->assertNull($renderer->render(new ModuleModel()));
    }
}
