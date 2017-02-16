<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\FragmentType\FragmentTypesProviderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Fragment registry compiler pass.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistryPass implements CompilerPassInterface
{
    /**
     * Collect all the fragments types provider and add them and their respectively
     * tagged services to the fragment registry.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('contao.fragment_registry')) {
            return;
        }

        $fragmentRegistry = $container->findDefinition('contao.fragment_registry');

        if (!$this->classImplementsInterface($fragmentRegistry->getClass(), FragmentRegistryInterface::class)) {
            return;
        }

        // Search type providers
        $typeProviders = $container->findTaggedServiceIds('contao.fragment_types_provider');

        foreach ($typeProviders as $id => $tags) {

            $fragmentTypeProvider = $container->findDefinition($id);

            if (!$this->classImplementsInterface($fragmentTypeProvider->getClass(), FragmentTypesProviderInterface::class)) {
                throw new LogicException(sprintf('The class "%s" was registered as "contao.fragment_types_provider" but does not implement the interface "%s".',
                    $fragmentTypeProvider->getClass(),
                    FragmentTypesProviderInterface::class
                ));
            }

            // Resolve the fragment type provider to ask for the types
            /* @var FragmentTypesProviderInterface $fragmentTypeProvider */
            $fragmentTypeProvider = $container->resolveServices($fragmentTypeProvider);

            foreach ($fragmentTypeProvider->getFragmentTypes() as $fragmentTypeInterface => $tag) {

                // Register the type
                $fragmentRegistry->addMethodCall('addFragmentType', [$fragmentTypeInterface]);

                $taggedServices = $container->findTaggedServiceIds($tag);

                foreach ($taggedServices as $id => $tags) {

                    $fragment = $container->findDefinition($id);

                    if (!$this->classImplementsInterface($fragment->getClass(), $fragmentTypeInterface)) {
                        throw new LogicException(sprintf('The class "%s" was registered as "%s" but does not implement the interface "%s".',
                            $fragment->getClass(),
                            $tag,
                            $fragmentTypeInterface
                        ));
                    }

                    $fragmentRegistry->addMethodCall('addFragment', [new Reference($id)]);
                }
            }
        }
    }

    /**
     * Checks if a given class name implements a given interface name.
     *
     * @param string $class
     * @param string $interface
     *
     * @return bool
     */
    private function classImplementsInterface($class, $interface)
    {
        $ref = new \ReflectionClass($class);

        return $ref->implementsInterface($interface);
    }
}
