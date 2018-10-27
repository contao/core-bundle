<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\DependencyInjection\Compiler;

use Contao\CoreBundle\DependencyInjection\Compiler\DataContainerCallbackPass;
use Contao\CoreBundle\EventListener\DataContainerCallbackListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class DataContainerCallbackPassTest extends TestCase
{
    public function testRegistersTheHookListeners(): void
    {
        $attributes = [
            'table' => 'tl_page',
            'target' => 'config.onload_callback',
            'method' => 'onLoadPage',
            'priority' => 10,
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        10 => [
                            ['test.callback_listener', 'onLoadPage'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testMakesHookListenersPublic(): void
    {
        $attributes = [
            'table' => 'tl_page',
            'target' => 'config.onload_callback',
            'method' => 'onLoadPage',
            'priority' => 10,
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);
        $definition->setPublic(false);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $this->assertFalse($container->findDefinition('test.callback_listener')->isPublic());

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertTrue($container->findDefinition('test.callback_listener')->isPublic());
    }

    public function testGeneratesMethodNameIfNoneGiven(): void
    {
        $attributes = [
            'table' => 'tl_page',
            'target' => 'config.onload_callback',
            'priority' => 10,
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        10 => [
                            ['test.callback_listener', 'onLoadCallback'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testSetsTheDefaultPriorityIfNoPriorityGiven(): void
    {
        $attributes = [
            'table' => 'tl_page',
            'target' => 'config.onload_callback',
            'method' => 'onLoadPage',
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        0 => [
                            ['test.callback_listener', 'onLoadPage'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testAppendsCallbackSuffixIfNotGiven(): void
    {
        $attributes = [
            'table' => 'tl_page',
            'target' => 'config.onload',
            'priority' => 10,
            'method' => 'onLoadPage',
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        10 => [
                            ['test.callback_listener', 'onLoadPage'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testDoesNotAppendCallbackSuffixForWizard(): void
    {
        $attributes = [
            'table' => 'tl_content',
            'target' => 'fields.article.wizard',
            'priority' => 10,
            'method' => 'onArticleWizard',
        ];

        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', $attributes);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_content' => [
                    'fields.article.wizard' => [
                        10 => [
                            ['test.callback_listener', 'onArticleWizard'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testHandlesMultipleCallbacks(): void
    {
        $definition = new Definition('Test\CallbackListener');

        $definition->addTag(
            'contao.callback',
            [
                'table' => 'tl_page',
                'target' => 'config.onload',
                'method' => 'loadFirst',
            ]
        );

        $definition->addTag(
            'contao.callback',
            [
                'table' => 'tl_page',
                'target' => 'config.onload',
                'method' => 'loadSecond',
            ]
        );

        $definition->addTag(
            'contao.callback',
            [
                'table' => 'tl_article',
                'target' => 'fields.title.load',
            ]
        );

        $definition->addTag(
            'contao.callback',
            [
                'table' => 'tl_article',
                'target' => 'fields.title.save',
            ]
        );

        $definition->addTag(
            'contao.callback',
            [
                'table' => 'tl_content',
                'target' => 'list.label.label_callback',
            ]
        );

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        0 => [
                            ['test.callback_listener', 'loadFirst'],
                            ['test.callback_listener', 'loadSecond'],
                        ],
                    ],
                ],
                'tl_article' => [
                    'fields.title.load_callback' => [
                        0 => [
                            ['test.callback_listener', 'onLoadCallback'],
                        ],
                    ],
                    'fields.title.save_callback' => [
                        0 => [
                            ['test.callback_listener', 'onSaveCallback'],
                        ],
                    ],
                ],
                'tl_content' => [
                    'list.label.label_callback' => [
                        0 => [
                            ['test.callback_listener', 'onLabelCallback'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testAddsTheCallbacksByPriority(): void
    {
        $definitionA = new Definition('Test\CallbackListenerA');

        $definitionA->addTag(
            'contao.callback',
            [
                'table' => 'tl_page',
                'target' => 'config.onload',
                'priority' => 10,
            ]
        );

        $definitionB = new Definition('Test\CallbackListenerB');

        $definitionB->addTag(
            'contao.callback',
            [
                'table' => 'tl_page',
                'target' => 'config.onload',
                'method' => 'onLoadFirst',
                'priority' => 10,
            ]
        );

        $definitionB->addTag(
            'contao.callback',
            [
                'table' => 'tl_page',
                'target' => 'config.onload',
                'method' => 'onLoadSecond',
                'priority' => 100,
            ]
        );

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener.a', $definitionA);
        $container->setDefinition('test.callback_listener.b', $definitionB);

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $this->assertSame(
            [
                'tl_page' => [
                    'config.onload_callback' => [
                        10 => [
                            ['test.callback_listener.a', 'onLoadCallback'],
                            ['test.callback_listener.b', 'onLoadFirst'],
                        ],
                        100 => [
                            ['test.callback_listener.b', 'onLoadSecond'],
                        ],
                    ],
                ],
            ],
            $this->getCallbacksFromDefinition($container)[0]
        );
    }

    public function testDoesNothingIfThereIsNoListener(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->method('hasDefinition')
            ->with('contao.listener.data_container_callback')
            ->willReturn(false)
        ;

        $container
            ->expects($this->never())
            ->method('findTaggedServiceIds')
        ;

        $pass = new DataContainerCallbackPass();
        $pass->process($container);
    }

    public function testDoesNothingIfThereAreNoHooks(): void
    {
        $container = $this->getContainerBuilder();

        $pass = new DataContainerCallbackPass();
        $pass->process($container);

        $definition = $container->getDefinition('contao.listener.data_container_callback');

        $this->assertEmpty($definition->getMethodCalls());
    }

    public function testFailsIfTheTableAttributeIsMissing(): void
    {
        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', ['target' => 'config.onload']);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();

        $this->expectException(InvalidConfigurationException::class);

        $pass->process($container);
    }

    public function testFailsIfTheTargetAttributeIsMissing(): void
    {
        $definition = new Definition('Test\CallbackListener');
        $definition->addTag('contao.callback', ['table' => 'tl_page']);

        $container = $this->getContainerBuilder();
        $container->setDefinition('test.callback_listener', $definition);

        $pass = new DataContainerCallbackPass();

        $this->expectException(InvalidConfigurationException::class);

        $pass->process($container);
    }

    /**
     * @return array<int,array<int,string[]>>
     */
    private function getCallbacksFromDefinition(ContainerBuilder $container): array
    {
        $this->assertTrue($container->hasDefinition('contao.listener.data_container_callback'));

        $definition = $container->getDefinition('contao.listener.data_container_callback');
        $methodCalls = $definition->getMethodCalls();

        $this->assertInternalType('array', $methodCalls);
        $this->assertSame('setCallbacks', $methodCalls[0][0]);
        $this->assertInternalType('array', $methodCalls[0][1]);

        return $methodCalls[0][1];
    }

    /**
     * Returns the container builder with a dummy contao.framework definition.
     */
    private function getContainerBuilder(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->setDefinition(
            'contao.listener.data_container_callback',
            new Definition(DataContainerCallbackListener::class, [])
        );

        return $container;
    }
}
