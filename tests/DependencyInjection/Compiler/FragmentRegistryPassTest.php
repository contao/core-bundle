<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Tests the FragmentRegistryPass.
 *
 * @author Yanick Witschi
 */
class FragmentRegistryPassTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $pass = new FragmentRegistryPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass', $pass);
    }

    /**
     * Tests if fragments and fragment renderers are registered properly.
     */
    public function testFragmentsAndFragmentRenderersAreRegisteredProperly()
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $container,
            new FileLocator([
                __DIR__ . '/../../../src/Resources/config',
                __DIR__ . '/../../Fixtures/FragmentRegistry'
            ])
        );

        // This contains the real config
        $loader->load('services.yml');

        // This contains fixture data
        $loader->load('example.yml');

        $pass = new FragmentRegistryPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('contao.fragment.registry'));
        $this->assertSame('Contao\CoreBundle\FragmentRegistry\FragmentRegistry',
            $container->getDefinition('contao.fragment.registry')->getClass()
        );

        $this->assertTrue($container->hasDefinition('contao.fragment.renderer.frontend_module.default'));
        $this->assertSame('Contao\CoreBundle\FragmentRegistry\FrontendModule\DefaultFrontendModuleRenderer',
            $container->getDefinition('contao.fragment.renderer.frontend_module.default')->getClass()
        );

        $this->assertContains('contao.fragment.renderer.frontend_module',
            array_keys($container->getDefinition('contao.fragment.renderer.frontend_module.default')->getTags())
        );

        $this->assertTrue($container->hasDefinition('contao.fragment.renderer.frontend_module.default'));
        $this->assertSame('Contao\CoreBundle\FragmentRegistry\FrontendModule\DefaultFrontendModuleRenderer',
            $container->getDefinition('contao.fragment.renderer.frontend_module.default')->getClass()
        );

        $this->assertContains('contao.fragment.renderer.frontend_module',
            array_keys($container->getDefinition('contao.fragment.renderer.frontend_module.default')->getTags())
        );

        $this->assertContains('contao.fragment.renderer.content_element',
            array_keys($container->getDefinition('contao.fragment.renderer.content_element.default')->getTags())
        );

        $this->assertTrue($container->hasDefinition('contao.fragment.renderer.content_element.default'));
        $this->assertSame('Contao\CoreBundle\FragmentRegistry\ContentElement\DefaultContentElementRenderer',
            $container->getDefinition('contao.fragment.renderer.content_element.default')->getClass()
        );

        $this->assertContains('contao.fragment.renderer.content_element',
            array_keys($container->getDefinition('contao.fragment.renderer.content_element.default')->getTags())
        );

        $this->assertContains('contao.fragment.renderer.page_type',
            array_keys($container->getDefinition('contao.fragment.renderer.page_type.default')->getTags())
        );

        $this->assertTrue($container->hasDefinition('contao.fragment.renderer.page_type.default'));
        $this->assertSame('Contao\CoreBundle\FragmentRegistry\PageType\DefaultPageTypeRenderer',
            $container->getDefinition('contao.fragment.renderer.page_type.default')->getClass()
        );

        $this->assertContains('contao.fragment.renderer.page_type',
            array_keys($container->getDefinition('contao.fragment.renderer.page_type.default')->getTags())
        );

        $methodCalls = $container->getDefinition('contao.fragment.registry')->getMethodCalls();

        $this->assertSame('addFragment', $methodCalls[0][0]);
        $this->assertSame('contao.fragment.frontend_module.navigation_trivial', $methodCalls[0][1][0]);
        $this->assertSame([
            'type' => 'navigation_trivial',
            'category' => 'navigationMenu',
            'controller' => 'AppBundle\TestTrivialModule',
            'tag' => 'contao.fragment.frontend_module'
        ], $methodCalls[0][1][2]);

        $this->assertSame('addFragment', $methodCalls[1][0]);
        $this->assertSame('contao.fragment.frontend_module.navigation_esi', $methodCalls[1][1][0]);
        $this->assertSame([
            'type' => 'navigation_esi',
            'category' => 'navigationMenu',
            'renderStrategy' => 'esi',
            'controller' => 'AppBundle\TestEsiModule',
            'tag' => 'contao.fragment.frontend_module'
        ], $methodCalls[1][1][2]);

        $this->assertSame('addFragment', $methodCalls[2][0]);
        $this->assertSame('contao.fragment.page_type.super_page', $methodCalls[2][1][0]);
        $this->assertSame([
            'type' => 'super_page',
            'controller' => 'AppBundle\SuperPageType',
            'tag' => 'contao.fragment.page_type'
        ], $methodCalls[2][1][2]);

        $this->assertSame('addFragment', $methodCalls[3][0]);
        $this->assertSame('contao.fragment.content_element.other', $methodCalls[3][1][0]);
        $this->assertSame([
            'type' => 'other',
            'category' => 'text',
            'renderStrategy' => 'esi',
            'controller' => 'other_controller:foobarAction', // Validates method option
            'tag' => 'contao.fragment.content_element'
        ], $methodCalls[3][1][2]);
    }
}
