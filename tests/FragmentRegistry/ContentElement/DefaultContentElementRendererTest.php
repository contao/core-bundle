<?php

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
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Class DefaultContentElementRendererTest.
 *
 * @author Yanick Witschi
 */
class DefaultContentElementRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $registry = new FragmentRegistry();
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $requestStack = new RequestStack();

        $renderer = new DefaultContentElementRenderer($registry, $fragmentHandler, $requestStack);

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\ContentElement\DefaultContentElementRenderer', $renderer);
    }

    public function testSupports()
    {
        $registry = new FragmentRegistry();
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $requestStack = new RequestStack();

        $renderer = new DefaultContentElementRenderer($registry, $fragmentHandler, $requestStack);

        $model = new ContentModel();

        $this->assertTrue($renderer->supports($model));
    }

    public function testRender()
    {
        $expectedControllerReference = new ControllerReference('test', [
            'contentModel' => 42,
            'inColumn' => 'whateverColumn',
            'scope' => 'scope',
        ]);

        $registry = new FragmentRegistry();
        $registry->addFragment(FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT.'.identifier',
            new \stdClass(), [
                'tag' => FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT,
                'type' => 'test',
                'controller' => 'test',
                'category' => 'text',
            ]);

        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $fragmentHandler->expects($this->once())
            ->method('render')
            ->with($this->equalTo($expectedControllerReference));

        $requestStack = new RequestStack();

        $renderer = new DefaultContentElementRenderer($registry, $fragmentHandler, $requestStack);

        $model = new ContentModel();
        $model->setRow(['id' => 42, 'type' => 'identifier']);

        $renderer->render($model, 'whateverColumn', 'scope');
    }
}
