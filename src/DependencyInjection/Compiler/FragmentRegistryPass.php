<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Controller\FragmentRegistry\ContentElementInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\FrontendModuleInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\InsertTagInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\PageTypeInterface;
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
    const TAGS_FOR_INTERFACE = [
        'contao.content_element' => ContentElementInterface::class,
        'contao.frontend_module' => FrontendModuleInterface::class,
        'contao.insert_tag' => InsertTagInterface::class,
        'contao.page_type' => PageTypeInterface::class,
    ];

    /**
     * Collect all the fragments and add them to the fragment registry.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('contao.fragment_registry')) {
            return;
        }

        $definition = $container->findDefinition('contao.fragment_registry');

        if (!is_a($definition->getClass(), FragmentRegistryInterface::class, true)) {
            return;
        }

        foreach (self::TAGS_FOR_INTERFACE as $tag => $interface) {
            $taggedServices = $container->findTaggedServiceIds($tag);

            foreach ($taggedServices as $id => $tags) {

                $fragment = $container->findDefinition($id);

                if (!is_a($fragment->getClass(), $interface, true)) {
                    throw new LogicException(sprintf('The class "%s" was registered as "%s" but does not implement the interface "%s".',
                        $fragment->getClass(),
                        $tag,
                        $interface
                    ));
                }

                $definition->addMethodCall('addFragment', array(new Reference($id)));
            }

        }
    }
}
