<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\HttpKernel\ContaoKernelInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add route loaders by the contao.routing.loader tag.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class AddContaoRouteLoadersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('contao.routing.loader');
        uasort(
            $services,
            function (array $left, array $right) {
                $leftPriority  = isset($left['priority']) ? $left['priority'] : 0;
                $rightPriority = isset($right['priority']) ? $right['priority'] : 0;

                return $rightPriority - $leftPriority;
            }
        );
        $services = array_keys($services);

        $bundlesLoaderDefinition = $container->findDefinition('contao.routing.bundles');
        $bundlesLoaderDefinition->addMethodCall('setServiceIds', [$services]);
    }
}
