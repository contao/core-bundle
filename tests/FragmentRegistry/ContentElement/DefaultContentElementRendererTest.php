<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\ContentElement\DefaultContentElementRenderer;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class DefaultContentElementRendererTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(
            'Contao\CoreBundle\FragmentRegistry\ContentElement\DefaultContentElementRenderer',
            $this->mockRenderer()
        );
    }

    public function testSupportsContentModels(): void
    {
        $this->assertTrue($this->mockRenderer()->supports(new ContentModel()));
    }

    public function testRendersContentModels(): void
    {
        $expectedControllerReference = new ControllerReference(
            'test',
            [
                'contentModel' => 42,
                'inColumn' => 'main',
                'scope' => 'scope',
            ]
        );

        $handler = $this->createMock(FragmentHandler::class);

        $handler
            ->expects($this->once())
            ->method('render')
            ->with($this->equalTo($expectedControllerReference))
        ;

        $model = new ContentModel();
        $model->setRow(['id' => 42, 'type' => 'identifier']);

        $registry = new FragmentRegistry();

        $registry->addFragment(
            FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT.'.identifier',
            new \stdClass(),
            [
                'tag' => FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT,
                'type' => 'test',
                'controller' => 'test',
                'category' => 'text',
            ]
        );

        $renderer = $this->mockRenderer($registry, $handler);
        $renderer->render($model, 'main', 'scope');
    }

    /**
     * Mocks a default content element renderer.
     *
     * @param FragmentRegistryInterface|null $registry
     * @param FragmentHandler|null           $handler
     *
     * @return DefaultContentElementRenderer
     */
    private function mockRenderer(FragmentRegistryInterface $registry = null, FragmentHandler $handler = null): DefaultContentElementRenderer
    {
        if (null === $registry) {
            $registry = new FragmentRegistry();
        }

        if (null === $handler) {
            $handler = $this->createMock(FragmentHandler::class);
        }

        return new DefaultContentElementRenderer($registry, $handler, new RequestStack());
    }
}
