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
 * Add route providers by service tag.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class AddRouteProvidersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainedProviderDefinition = $container->findDefinition('contao.routing.chain_provider');

        $services = $container->findTaggedServiceIds('cmf_routing.provider');
        uasort(
            $services,
            function (array $left, array $right) {
                $leftPriority  = isset($left['priority']) ? $left['priority'] : 0;
                $rightPriority = isset($right['priority']) ? $right['priority'] : 0;

                return $rightPriority - $leftPriority;
            }
        );
        $services = array_keys($services);

        foreach ($services as $serviceId) {
            $chainedProviderDefinition->addMethodCall(
                'addProvider',
                [new Reference($serviceId)]
            );
        }

        if (1 === count($services)) {
            // replace the provider alias of only one provider is defined
            $container->setAlias('contao.routing.provider', array_shift($services));
        }
    }
}
