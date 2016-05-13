<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\Twig\Loader\ContaoLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * TwigLoaderPass
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class TwigLoaderPass implements CompilerPassInterface
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $contaoRoot = dirname($container->getParameter('kernel.root_dir'));
        $localPath = $contaoRoot . '/templates';

        $templates = Finder::create()
            ->files()
            ->path('templates/')
            ->name('*.twig')
            ->in($container->getParameter('contao.resources_paths'))
        ;

        $paths = array_map(
            function (SplFileInfo $file) {
                return $file->getPath();
            },
            iterator_to_array($templates)
        );
        $paths = array_reverse(array_unique(array_values($paths)));

        $service = $container->getDefinition('contao.twig.loader');
        $service->addMethodCall('setPaths', [$paths, ContaoLoader::BUNDLE_NAMESPACE]);
        $service->addMethodCall('setPaths', [$localPath, ContaoLoader::LOCAL_NAMESPACE]);
        array_unshift($paths, $localPath);
        $service->setArguments([$paths]);
    }
}
