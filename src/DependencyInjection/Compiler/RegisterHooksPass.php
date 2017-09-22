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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Find hook services and store them in an parameter.
 *
 * @author David Molineus <https://github.com/dmolineus>
 */
class RegisterHooksPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('contao.hook');
        $hooks      = [];

        foreach ($serviceIds as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $this->guardRequiredAttributesExist($serviceId, $attributes);

                $priority = $attributes['priority'] ?? 0;
                $hook     = $attributes['hook'];

                $hooks[$hook][$priority][] = [$serviceId, $attributes['method']];
            }
        }

        if (count($hooks) > 0) {
            // Apply priority sorting.
            krsort($hooks);

            $container->setParameter('contao.hook_listeners', $hooks);
        }
    }

    /**
     * Guard that required attributes (hook and method) are defined.
     *
     * @param string $serviceId  Service id.
     * @param array  $attributes Tag attributes.
     *
     * @throws InvalidConfigurationException When an attribute is missing.
     */
    private function guardRequiredAttributesExist(string $serviceId, array $attributes): void
    {
        if (!isset($attributes['hook'])) {
            throw new InvalidConfigurationException(
                sprintf('Missing hook attribute in tagged hook service with service id "%s"', $serviceId)
            );
        }

        if (!isset($attributes['method'])) {
            throw new InvalidConfigurationException(
                sprintf('Missing method attribute in tagged hook service with service id "%s"', $serviceId)
            );
        }
    }
}
