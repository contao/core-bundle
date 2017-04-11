<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers the picker menu providers.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PickerMenuProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('contao.menu.picker_menu_builder')) {
            return;
        }

        $definition = $container->findDefinition('contao.menu.picker_menu_builder');
        $services = $container->findTaggedServiceIds('contao.picker_menu_provider');

        foreach ($services as $id => $tags) {
            $definition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}
