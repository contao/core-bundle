<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\FragmentRegistry\FrontendModule\DefaultFrontendModuleRenderer;
use Contao\CoreBundle\Tests\TestCase;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Class DefaultFrontendModuleRendererTest
 *
 * @author Yanick Witschi
 */
class DefaultFrontendModuleRendererTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $registry = new FragmentRegistry();
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $requestStack = new RequestStack();

        $renderer = new DefaultFrontendModuleRenderer($registry, $fragmentHandler, $requestStack);

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\FrontendModule\DefaultFrontendModuleRenderer', $renderer);
    }

    public function testSupports()
    {
        $registry = new FragmentRegistry();
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $requestStack = new RequestStack();

        $renderer = new DefaultFrontendModuleRenderer($registry, $fragmentHandler, $requestStack);

        $model = new ModuleModel();

        $this->assertTrue($renderer->supports($model));
    }

    public function testRender()
    {
        $expectedControllerReference = new ControllerReference('test', [
            'moduleModel' => 42,
            'inColumn' => 'whateverColumn',
            'scope' => 'scope'
        ]);

        $registry = new FragmentRegistry();
        $registry->addFragment(FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE . '.identifier',
            new \stdClass(), [
                'tag' => FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE,
                'type' => 'test',
                'controller' => 'test',
                'category' => 'navigationMod'
            ]);

        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $fragmentHandler->expects($this->once())
            ->method('render')
            ->with($this->equalTo($expectedControllerReference));

        $requestStack = new RequestStack();

        $renderer = new DefaultFrontendModuleRenderer($registry, $fragmentHandler, $requestStack);

        $model = new ModuleModel();
        $model->setRow(['id' => 42, 'type' => 'identifier']);

        $renderer->render($model, 'whateverColumn', 'scope');
    }
}
