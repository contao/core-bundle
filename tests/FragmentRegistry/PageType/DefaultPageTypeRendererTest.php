<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\PageType;

use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\FragmentRegistry\PageType\DefaultPageTypeRenderer;
use Contao\CoreBundle\Tests\TestCase;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Class DefaultPageTypeRendererTest.
 *
 * @author Yanick Witschi
 */
class DefaultPageTypeRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(
            'Contao\CoreBundle\FragmentRegistry\PageType\DefaultPageTypeRenderer',
            $this->mockRenderer()
        );
    }

    public function testSupportsPageModels()
    {
        $this->assertTrue($this->mockRenderer()->supports(new PageModel()));
    }

    public function testRendersPageModels()
    {
        $page = new PageModel();
        $page->setRow(['id' => 42]);

        $GLOBALS['objPage'] = $page;

        $expectedControllerReference = new ControllerReference(
            'test',
            [
                'pageModel' => 42,
            ]
        );

        $registry = new FragmentRegistry();

        $registry->addFragment(
            FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE.'.identifier',
            new \stdClass(),
            [
                'tag' => FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE,
                'type' => 'test',
                'controller' => 'test',
            ]
        );

        $fragmentHandler = $this->createMock(FragmentHandler::class);

        $fragmentHandler
            ->expects($this->once())
            ->method('render')
            ->with($this->equalTo($expectedControllerReference))
        ;

        $model = new PageModel();
        $model->setRow(['id' => 42, 'type' => 'identifier']);

        $renderer = $this->mockRenderer($registry, $fragmentHandler);
        $renderer->render($model);
    }

    /**
     * Mocks a default front end module renderer.
     *
     * @param FragmentRegistryInterface|null $registry
     * @param FragmentHandler|null           $handler
     *
     * @return DefaultPageTypeRenderer
     */
    private function mockRenderer(FragmentRegistryInterface $registry = null, FragmentHandler $handler = null)
    {
        if (null === $registry) {
            $registry = new FragmentRegistry();
        }

        if (null === $handler) {
            $handler = $this->createMock(FragmentHandler::class);
        }

        return new DefaultPageTypeRenderer($registry, $handler, new RequestStack());
    }
}
