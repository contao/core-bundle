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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Fragment registry compiler pass.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    const FRAGMENT_REGISTRY = 'contao.fragment.registry';

    const TAG_FRAGMENT_FRONTEND_MODULE = 'contao.fragment.renderer.frontend_module';
    const TAG_FRAGMENT_PAGE_TYPE = 'contao.fragment.renderer.page_type';
    const TAG_FRAGMENT_CONTENT_ELEMENT = 'contao.fragment.renderer.content_element';

    const RENDERER_FRONTEND_MODULE = 'contao.fragment.renderer.frontend_module.delegating';
    const RENDERER_PAGE_TYPE = 'contao.fragment.renderer.page_type.delegating';
    const RENDERER_CONTENT_ELEMENT = 'contao.fragment.renderer.content_element.delegating';

    const TAG_RENDERER_FRONTEND_MODULE = 'contao.fragment.renderer.frontend_module';
    const TAG_RENDERER_PAGE_TYPE = 'contao.fragment.renderer.page_type';
    const TAG_RENDERER_CONTENT_ELEMENT = 'contao.fragment.renderer.content_element';

    /**
     * @var Definition
     */
    private $fragmentRegistry;

    /**
     * Collect all the fragments and fragment renderers.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::FRAGMENT_REGISTRY)) {
            return;
        }

        $this->fragmentRegistry = $container->findDefinition(self::FRAGMENT_REGISTRY);

        if (!$this->classImplementsInterface(
            $this->fragmentRegistry->getClass(), FragmentRegistryInterface::class)
        ) {
            return;
        }

        foreach ([
            self::TAG_FRAGMENT_FRONTEND_MODULE,
            self::TAG_FRAGMENT_PAGE_TYPE,
            self::TAG_FRAGMENT_CONTENT_ELEMENT,
        ] as $tag) {
            $this->registerFragments($container, $tag);

        }

        foreach ([
            self::RENDERER_FRONTEND_MODULE => self::TAG_RENDERER_FRONTEND_MODULE,
            self::RENDERER_PAGE_TYPE => self::TAG_RENDERER_PAGE_TYPE,
            self::RENDERER_CONTENT_ELEMENT => self::TAG_FRAGMENT_CONTENT_ELEMENT,
        ] as $renderer => $tag) {
            $this->registerFragmentRenderers($container, $renderer, $tag);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $tag
     */
    private function registerFragments(ContainerBuilder $container, string $tag)
    {
        $fragments = $this->findAndSortTaggedServices($tag, $container);

        foreach ($fragments as $priority => $reference) {

            $fragment = $container->findDefinition($reference);
            $fragmentOptions = $fragment->getTag($tag)[0];

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
            $this->fragmentRegistry->addMethodCall('addFragment', [$fragmentIdentifier, $reference, $fragmentOptions]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $renderer
     * @param string           $tag
     */
    private function registerFragmentRenderers(ContainerBuilder $container, string $renderer, string $tag)
    {
        if (!$container->has($renderer)) {
            return;
        }

        $renderer = $container->findDefinition($renderer);
        $renderer->setArgument(0, $this->findAndSortTaggedServices($tag, $container));
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
