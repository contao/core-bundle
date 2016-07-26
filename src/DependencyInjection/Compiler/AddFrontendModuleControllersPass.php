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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Registers the Contao front end module controllers.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class AddFrontendModuleControllersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $feController     = $container->getDefinition('contao.controller.frontend_module');
        $feModControllers = $container->findTaggedServiceIds('contao.frontend_module');

        foreach ($feModControllers as $id => $tagAttributes) {

            if (!isset($tagAttributes[0]['type']) && !isset($tagAttributes[0]['method'])) {
                throw new InvalidArgumentException('A "contao.frontend_module" controller has to provide "type" and "method" attributes.');
            }

            $feController->addMethodCall('setController', [
                    $tagAttributes[0]['type'],
                    new Definition(
                        'Symfony\Component\HttpKernel\Controller\ControllerReference',
                        [$id . ':' . $tagAttributes[0]['method']]
                    )
                ]
            );
        }
    }
}
