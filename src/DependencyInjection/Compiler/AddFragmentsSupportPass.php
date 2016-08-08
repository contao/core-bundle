<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the composer packages and versions to the container.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class AddFragmentsSupportPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // If Symfony's fragment listener is not enabled, fragments are not used
        // and we thus don't want to have any special handling here
        if (!$container->hasDefinition('fragment.listener')) {
            return;
        }

        // If our own search index listener is not enabled, we don't add any
        // special handling here either
        if (!$container->hasDefinition('contao.listener.add_to_search_index')) {
            return;
        }

        $rgxp = '/' . preg_quote($container->getParameter('fragment.path'), '/') . '/';

        $searchListener = $container->findDefinition('contao.listener.add_to_search_index');
        $searchListener->addMethodCall('setIgnorePathRegexes', [[$rgxp]]);
    }
}
