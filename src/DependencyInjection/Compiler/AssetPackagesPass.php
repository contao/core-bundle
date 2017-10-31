<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Asset\ContaoAssetPackage;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds asset packages to the container.
 */
class AssetPackagesPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('assets.packages')) {
            return;
        }

        $this->addComponents($container);
    }

    /**
     * Adds each Contao component as asset package.
     *
     * @param ContainerBuilder $container
     */
    private function addComponents(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('kernel.packages')) {
            return;
        }

        $packages = $container->getDefinition('assets.packages');
        $context = new Reference('contao.assets.assets_context');

        foreach ($container->getParameter('kernel.packages') as $name => $version) {
            list($vendor, $packageName) = explode('/', $name, 2);

            if ('contao-components' !== $vendor) {
                continue;
            }

            $serviceId = 'assets._package_'.$name;
            $basePath = 'assets/' . $packageName;
            $version = $this->createPackageVersion($container, $version, $name);
            $container->setDefinition($serviceId, $this->createPackageDefinition($basePath, $version, $context));

            $packages->addMethodCall('addPackage', [$name, new Reference($serviceId)]);
        }
    }

    /**
     * Creates a definition for an asset package.
     *
     * @param string    $basePath
     * @param Reference $version
     * @param Reference $context
     *
     * @return Definition
     */
    private function createPackageDefinition(string $basePath, Reference $version, Reference $context): Definition
    {
        $package = new ChildDefinition('assets.path_package');
        $package
            ->setPublic(false)
            ->replaceArgument(0, $basePath)
            ->replaceArgument(1, $version)
            ->replaceArgument(2, $context)
        ;

        return $package;
    }

    /**
     * Creates a version strategy for an asset package.
     *
     * @param ContainerBuilder $container
     * @param string           $version
     * @param string           $name
     *
     * @return Reference
     */
    private function createPackageVersion(ContainerBuilder $container, string $version, string $name): Reference
    {
        $def = new ChildDefinition('assets.static_version_strategy');
        $def->replaceArgument(0, $version);

        $container->setDefinition('assets._version_'.$name, $def);

        return new Reference('assets._version_'.$name);
    }
}
