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
 * TwigEnvironmentPass
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class TwigEnvironmentPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('contao.twig');

        $args = $definition->getArguments();
        $args[1]['autoescape'] = false;

        $definition->setArguments($args);
    }
}
