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
use Contao\CoreBundle\Controller\FrontendModule\FrontendModuleRendererInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Fragment registry compiler pass.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * Collect all the fragments and fragment renderers.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerFragments($container);
        $this->registerFrontendModuleRenderers($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function registerFragments(ContainerBuilder $container)
    {
        if (!$container->has('contao.fragment.registry')) {
            return;
        }

        $fragmentRegistry = $container->findDefinition('contao.fragment.registry');

        if (!$this->classImplementsInterface($fragmentRegistry->getClass(), FragmentRegistryInterface::class)) {
            return;
        }

        $fragments = $this->findAndSortTaggedServices('contao.fragment', $container);

        foreach ($fragments as $priority => $reference) {

            $fragment = $container->findDefinition($reference);
            $fragmentOptions = $fragment->getTag('contao.fragment')[0];

            if (!isset($fragmentOptions['fragment']) || !isset($fragmentOptions['type'])) {
                throw new RuntimeException('A service tagged as "contao.fragment" must have a "fragment" and "type" attribute set.');
            }

            $fragmentOptions['controller'] = (string) $reference;

            // Support specific method on controller
            if (isset($fragmentOptions['method'])) {
                $fragmentOptions['controller'] .= ':' . $fragmentOptions['method'];
            }

            // Mark all fragments as lazy so they are lazy loaded using
            // the proxy manager (which is why we need to require it in the
            // composer.json (otherwise the lazy definition will just be ignored)
            $fragment->setLazy(true);

            $fragmentIdentifier = $fragmentOptions['fragment'] . '.' . $fragmentOptions['type'];
            $fragmentRegistry->addMethodCall('addFragment', [$fragmentIdentifier, $reference, $fragmentOptions]);
        }
    }

    private function registerFrontendModuleRenderers(ContainerBuilder $container)
    {
        if (!$container->has('contao.fragment.renderer.frontend.delegating')) {
            return;
        }

        $renderer = $container->findDefinition('contao.fragment.renderer.frontend.delegating');

        if (!$this->classImplementsInterface($renderer->getClass(), FrontendModuleRendererInterface::class)) {
            return;
        }

        $renderer->setArgument(0, $this->findAndSortTaggedServices('contao.fragment.renderer.frontend', $container));
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
