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
use Contao\CoreBundle\Framework\ContaoFramework;
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
    public function testSetHookListenersParameter(): void
    {
        $container = $this->createContainerBuilder();

        $definition = new Definition('Test\HookListener\AfterListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'     => 'initializeSystem',
                'method'   => 'onInitializeSystem',
                'priority' => 0,
            ]
        );

        $container->setDefinition('test.hook_listener.after', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $argument = $this->assertHookListenersAreRegistered($container);

        $this->assertArrayHasKey('initializeSystem', $argument);
        $this->assertArrayHasKey(0, $argument['initializeSystem']);

        $expected = [['test.hook_listener.after', 'onInitializeSystem']];
        $this->assertTrue($expected === $argument['initializeSystem'][0]);
    }

    /**
     * Tests the after parameter is given.
     */
    public function testPriorityIsZeroByDefaultParameter(): void
    {
        $container = $this->createContainerBuilder();

        $definition = new Definition('Test\HookListener\AfterListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'     => 'initializeSystem',
                'method'   => 'onInitializeSystem',
            ]
        );

        $container->setDefinition('test.hook_listener.after', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $argument = $this->assertHookListenersAreRegistered($container);

        $this->assertArrayHasKey('initializeSystem', $argument);
        $this->assertArrayHasKey(0, $argument['initializeSystem']);

        $expected = [['test.hook_listener.after', 'onInitializeSystem']];
        $this->assertTrue($expected === $argument['initializeSystem'][0]);
    }

    /**
     * Tests that multiple tags are handled.
     */
    public function testMultipleTagsAreHandled(): void
    {
        $container = $this->createContainerBuilder();

        $definition = new Definition('Test\HookListener');
        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'initializeSystem',
                'method' => 'onInitializeSystemFirst',
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
                'method' => 'onInitializeSystemSecond',
            ]
        );

        $definition->addTag(
            'contao.hook',
            [
                'hook'   => 'parseTemplate',
                'method' => 'onParseTemplate',
            ]
        );

        $container->setDefinition('test.hook_listener', $definition);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $argument = $this->assertHookListenersAreRegistered($container);

        $this->assertArrayHasKey('initializeSystem', $argument);
        $this->assertArrayHasKey(0, $argument['initializeSystem']);

        $this->assertArrayHasKey('generatePage', $argument);
        $this->assertArrayHasKey(0, $argument['generatePage']);

        $expected = [
            ['test.hook_listener', 'onInitializeSystemFirst'],
            ['test.hook_listener', 'onInitializeSystemSecond']
        ];

        $this->assertTrue($expected === $argument['initializeSystem'][0]);

        $expected = [['test.hook_listener', 'onGeneratePage']];
        $this->assertTrue($expected === $argument['generatePage'][0]);
    }

    /**
     * Tests that multiple tags are handled.
     */
    public function testMultipleDefinitionsWithPrioritiesAreSorted(): void
    {
        $container = $this->createContainerBuilder();

        $definitionA = new Definition('Test\HookListenerA');
        $definitionA->addTag(
            'contao.hook',
            [
                'hook'     => 'initializeSystem',
                'method'   => 'onInitializeSystem',
                'priority' => 10,
            ]
        );

        $definitionB = new Definition('Test\HookListenerB');
        $definitionB->addTag(
            'contao.hook',
            [
                'hook'     => 'initializeSystem',
                'method'   => 'onInitializeSystemLow',
                'priority' => 10,
            ]
        );

        $definitionB->addTag(
            'contao.hook',
            [
                'hook'     => 'initializeSystem',
                'method'   => 'onInitializeSystemHigh',
                'priority' => 100,
            ]
        );

        $container->setDefinition('test.hook_listener.a', $definitionA);
        $container->setDefinition('test.hook_listener.b', $definitionB);

        $pass = new RegisterHooksPass();
        $pass->process($container);

        $argument = $this->assertHookListenersAreRegistered($container);

        $this->assertArrayHasKey('initializeSystem', $argument);

        $this->assertArrayHasKey(10, $argument['initializeSystem']);
        $this->assertArrayHasKey(100, $argument['initializeSystem']);

        $expected = [
            100 => [['test.hook_listener.b', 'onInitializeSystemHigh']],
            10  => [
                ['test.hook_listener.a', 'onInitializeSystem'],
                ['test.hook_listener.b', 'onInitializeSystemLow']
            ],
        ];

        $this->assertTrue($expected === $argument['initializeSystem']);
    }

    /**
     * Test that exception is thrown for missing hook attribute.
     */
    public function testInvalidConfigurationExceptionForMissingHookAttribute(): void
    {
        $container = $this->createContainerBuilder();

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
        $container = $this->createContainerBuilder();

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

    /**
     * Assert hook listeners are registered and return them as array.
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function assertHookListenersAreRegistered(ContainerBuilder $container)
    {
        $this->assertTrue($container->hasDefinition('contao.framework'));

        $definition = $container->getDefinition('contao.framework');
        $argument   = $definition->getArgument(6);

        $this->assertTrue(is_array($argument));
        $this->assertTrue(count($argument) > 0);

        return $argument;
    }

    /**
     * Create the container builder with a dummy contao.framework definition.
     *
     * @return ContainerBuilder
     */
    private function createContainerBuilder()
    {
        $container = new ContainerBuilder();
        $container->setDefinition('contao.framework', new Definition(ContaoFramework::class, []));

        return $container;
    }
}
