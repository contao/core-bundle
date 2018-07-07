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

use Contao\ContentProxy;
use Contao\CoreBundle\DependencyInjection\Compiler\MapFragmentsToGlobalsPass;
use Contao\CoreBundle\EventListener\GlobalsMapListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MapFragmentsToGlobalsPassTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $pass = new MapFragmentsToGlobalsPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\MapFragmentsToGlobalsPass', $pass);
    }

    public function testMapsFragmentsToTheGlobalsArray(): void
    {
        $contentElement = new Definition();
        $contentElement->addTag('contao.content_element', ['category' => 'text', 'type' => 'foobar']);

        $frontendModule = new Definition();
        $frontendModule->addTag('contao.content_element', ['category' => 'module', 'type' => 'foobar']);

        $container = new ContainerBuilder();
        $container->setDefinition('app.fragments.content_element', $contentElement);
        $container->setDefinition('app.fragments.frontend_module', $frontendModule);

        $pass = new MapFragmentsToGlobalsPass();
        $pass->process($container);

        $definition = null;

        foreach ($container->getDefinitions() as $def) {
            if ($def->getClass() === GlobalsMapListener::class) {
                $definition = $def;
                break;
            }
        }

        $this->assertNotNull($definition);

        $this->assertSame(
            [
                'contao.hook' => [
                    [
                        'hook' => 'initializeSystem',
                        'priority' => 255,
                    ],
                ],
            ],
            $definition->getTags()
        );

        $this->assertSame(
            [
                'TL_CTE' => [
                    'text' => [
                        'foobar' => ContentProxy::class,
                    ],
                    'module' => [
                        'foobar' => ContentProxy::class,
                    ],
                ],
            ],
            $definition->getArguments()[0]
        );

        $this->assertTrue($definition->isPublic());
    }

    public function testFailsIfTheCategoryIsMissing(): void
    {
        $contentElement = new Definition();
        $contentElement->addTag('contao.content_element', ['type' => 'foobar']);

        $container = new ContainerBuilder();
        $container->setDefinition('app.fragments.content_element', $contentElement);

        $pass = new MapFragmentsToGlobalsPass();

        $this->expectException(InvalidConfigurationException::class);

        $pass->process($container);
    }

    public function testFailsIfTheTypeIsMissing(): void
    {
        $contentElement = new Definition();
        $contentElement->addTag('contao.content_element', ['category' => 'text']);

        $container = new ContainerBuilder();
        $container->setDefinition('app.fragments.content_element', $contentElement);

        $pass = new MapFragmentsToGlobalsPass();

        $this->expectException(InvalidConfigurationException::class);

        $pass->process($container);
    }
}
