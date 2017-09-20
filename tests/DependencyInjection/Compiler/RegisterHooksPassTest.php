<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\DependencyInjection\Compiler;

use Contao\CoreBundle\DependencyInjection\Compiler\RegisterHooksPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Tests the RegisterHooksPassTest class.
 *
 * @author David Molineus <https://github.com/dmolineus>
 */
class RegisterHooksPassTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $pass = new RegisterHooksPass();

        $this->assertInstanceOf(RegisterHooksPass::class, $pass);
    }

    /**
     * Tests the after parameter is given.
     */
    public function testSetHookListenerAfterParameter(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition('Test\HookListener\AfterListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'initializeSystem',
                'method' => 'onInitializeSystem'
            ]
        );

        $container->setDefinition('test.hook_listener.after', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $this->assertTrue($container->hasParameter('contao.hook_listeners.after'));
        $this->assertFalse($container->hasParameter('contao.hook_listeners.before'));

        $parameter = $container->getParameter('contao.hook_listeners.after');

        $this->assertArrayHasKey('initializeSystem', $parameter);
        $this->assertEquals([['test.hook_listener.after', 'onInitializeSystem']], $parameter['initializeSystem']);
    }


    /**
     * Tests the after parameter is given.
     */
    public function testSetHookListenerBeforeParameter(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition('Test\HookListener\BeforeListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'initializeSystem',
                'method' => 'onInitializeSystem',
                'before' => true,
            ]
        );

        $container->setDefinition('test.hook_listener.before', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $this->assertTrue($container->hasParameter('contao.hook_listeners.before'));
        $this->assertFalse($container->hasParameter('contao.hook_listeners.after'));

        $parameter = $container->getParameter('contao.hook_listeners.before');

        $this->assertArrayHasKey('initializeSystem', $parameter);
        $this->assertEquals([['test.hook_listener.before', 'onInitializeSystem']], $parameter['initializeSystem']);
    }

    /**
     * Tests that multiple tags are handled.
     */
    public function testMultipleTagsAreHandled(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition('Test\HookListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'initializeSystem',
                'method' => 'onInitializeSystemAfter',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'generatePage',
                'method' => 'onGeneratePage',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'initializeSystem',
                'method' => 'onInitializeSystemBefore',
                'before' => true,
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'parseTemplate',
                'method' => 'onParseTemplate',
                'before' => true,
            ]
        );

        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        // Test after parameter.
        $this->assertTrue($container->hasParameter('contao.hook_listeners.after'));
        $parameter = $container->getParameter('contao.hook_listeners.after');
        $this->assertArrayHasKey('initializeSystem', $parameter);
        $this->assertArrayHasKey('generatePage', $parameter);

        $this->assertEquals([['test.hook_listener', 'onInitializeSystemAfter']], $parameter['initializeSystem']);
        $this->assertEquals([['test.hook_listener', 'onGeneratePage']], $parameter['generatePage']);

        // Test before parameter.
        $this->assertTrue($container->hasParameter('contao.hook_listeners.before'));
        $parameter = $container->getParameter('contao.hook_listeners.before');
        $this->assertArrayHasKey('initializeSystem', $parameter);
        $this->assertArrayHasKey('parseTemplate', $parameter);

        $this->assertEquals([['test.hook_listener', 'onInitializeSystemBefore']], $parameter['initializeSystem']);
        $this->assertEquals([['test.hook_listener', 'onParseTemplate']], $parameter['parseTemplate']);
    }

    /**
     * Test that exception is thrown for missing hook attribute.
     */
    public function testInvalidConfigurationExceptionForMissingHookAttribute(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition('Test\HookListener');
        $definition->addTag(
            'contao.hook',
            [
                'method' => 'onInitializeSystemAfter',
            ]
        );

        $container->setDefinition('test.hook_listener', $definition);

        $this->expectException(InvalidConfigurationException::class);

        $pass = new RegisterHooksPass();
        $pass->process($container);
    }

    /**
     * Test that exception is thrown for missing method attribute.
     */
    public function testInvalidConfigurationExceptionForMissingMethodAttribute(): void
    {
        $container = new ContainerBuilder();

        $definition = new Definition('Test\HookListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook' => 'initializeSystem',
            ]
        );

        $container->setDefinition('test.hook_listener', $definition);

        $this->expectException(InvalidConfigurationException::class);

        $pass = new RegisterHooksPass();
        $pass->process($container);
    }
}
