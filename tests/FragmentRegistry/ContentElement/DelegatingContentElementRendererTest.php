<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\ContentElement;
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\ContentElement\ContentElementRendererInterface;
use Contao\CoreBundle\FragmentRegistry\ContentElement\DefaultContentElementRenderer;
use Contao\CoreBundle\FragmentRegistry\ContentElement\DelegatingContentElementRenderer;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Class DefaultContentElementRendererTest
 *
 * @author Yanick Witschi
 */
class DelegatingContentElementRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $renderer = new DelegatingContentElementRenderer([]);

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\ContentElement\DelegatingContentElementRenderer', $renderer);
    }

    public function testSupports()
    {
        $renderer1 = $this->createMock(ContentElementRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(true);

        $renderer2 = $this->createMock(ContentElementRendererInterface::class);
        $renderer2->expects($this->never())->method('supports');

        $renderer = new DelegatingContentElementRenderer([$renderer1, $renderer2]);
        $renderer->supports(new ContentModel());
    }

    public function testRender()
    {
        $renderer1 = $this->createMock(ContentElementRendererInterface::class);
        $renderer1->expects($this->once())->method('supports')->willReturn(true);
        $renderer1->expects($this->once())->method('render')->willReturn('foobar');

        $renderer2 = $this->createMock(ContentElementRendererInterface::class);
        $renderer2->expects($this->never())->method('supports');
        $renderer2->expects($this->never())->method('render');

        $renderer = new DelegatingContentElementRenderer([$renderer1, $renderer2]);
        $this->assertSame('foobar', $renderer->render(new ContentModel()));
    }
}
