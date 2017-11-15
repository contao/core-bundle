<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Fragment\FragmentConfig;
use Contao\CoreBundle\Fragment\FragmentPreHandlerInterface;
use Contao\CoreBundle\Fragment\Reference\ContentElementReference;
use Contao\CoreBundle\Fragment\Reference\FrontendModuleReference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers Contao fragments in the registry.
 *
 * For custom fragment tags, just override this class and replace the process() method.
 */
class RegisterFragmentsPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * Adds the fragments to the registry.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('contao.fragment.registry')) {
            return;
        }

        $this->registerFragments($container, ContentElementReference::TAG_NAME);
        $this->registerFragments($container, FrontendModuleReference::TAG_NAME);
    }

    /**
     * Registers the fragments.
     *
     * @param ContainerBuilder $container
     * @param string           $tag
     *
     * @throws InvalidConfigurationException
     */
    protected function registerFragments(ContainerBuilder $container, string $tag): void
    {
        $preHandlers = [];
        $registry = $container->findDefinition('contao.fragment.registry');

        foreach ($this->findAndSortTaggedServices($tag, $container) as $priority => $reference) {
            $definition = $container->findDefinition($reference);
            $tags = $definition->getTag($tag);

            foreach ($tags as &$attributes) {
                $attributes['type'] = $this->getFragmentType($definition, $attributes);

                $identifier = sprintf('%s.%s', $tag, $attributes['type']);
                $config = $this->createFragmentConfig($reference, $attributes);

                if (is_a($definition->getClass(), FragmentPreHandlerInterface::class, true)) {
                    $preHandlers[$identifier] = $reference;
                }

                $registry->addMethodCall('add', [$identifier, $config]);
            }

            unset($attributes);

            $definition->clearTag($tag);
            $definition->addTag($tag, $tags);
        }

        $this->addPreHandlers($container, $preHandlers);
    }

    /**
     * Creates a fragment configuration.
     *
     * @param Reference $reference
     * @param array     $attributes
     *
     * @return FragmentConfig
     */
    protected function createFragmentConfig(Reference $reference, array $attributes): FragmentConfig
    {
        $config = new FragmentConfig(
            $this->getControllerName($reference, $attributes),
            $attributes['renderStrategy'] ?? 'inline',
            ['ignore_errors' => true]
        );

        return $config;
    }

    /**
     * Creates controller name from service and method name.
     *
     * @param Reference $reference
     * @param array     $attributes
     *
     * @return string
     */
    protected function getControllerName(Reference $reference, array $attributes): string
    {
        $controller = (string) $reference;

        // Support a specific method on the controller
        if (isset($attributes['method'])) {
            $controller .= ':'.$attributes['method'];
        }

        return $controller;
    }

    /**
     * Adds additional factories to the preHandler ServiceLocator.
     *
     * @param ContainerBuilder $container
     * @param array            $handlers
     */
    protected function addPreHandlers(ContainerBuilder $container, array $handlers): void
    {
        if (!$container->hasDefinition('contao.fragment.pre_handlers')) {
            throw new \RuntimeException('Missing service definition for "contao.fragment.pre_handlers"');
        }

        $definition = $container->getDefinition('contao.fragment.pre_handlers');
        $definition->setArgument(0, array_merge($definition->getArgument(0), $handlers));
    }

    /**
     * Generates type from class name if not defined in the tag attributes.
     *
     * @param Definition $definition
     * @param array      $attributes
     *
     * @return string
     */
    protected function getFragmentType(Definition $definition, array $attributes): string
    {
        if (isset($attributes['type'])) {
            return (string) $attributes['type'];
        }

        $className = $definition->getClass();
        $className = substr($className, strrchr($className, '\\') + 1);

        if ('Controller' === substr($className, -10)) {
            $className = substr($className, 1, -10);
        }

        return Container::underscore($className);
    }
}
