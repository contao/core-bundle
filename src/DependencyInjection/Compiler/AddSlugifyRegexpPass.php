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
 * Adds the Slugify regexp depending on the folder_urls setting.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddSlugifyRegexpPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->getParameter('contao.folder_urls')) {
            return;
        }

        $definition = $container->findDefinition('cocur_slugify');

        $arguments = $definition->getArguments();
        $arguments[0]['regexp'] = '#([^A-Za-z0-9/.]|-)+#';

        $definition->setArguments($arguments);
    }
}
