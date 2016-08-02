<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel\Bundle;

use Mmoreram\SymfonyBundleDependencies\DependentBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Automatically register all legacy modules as dependencies.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
final class ContaoModuleAutoloadBundle extends Bundle implements DependentBundleInterface
{
    /**
     * @var string
     */
    public static $cacheDir;

    /**
     * @var ModuleBundleGenerator
     */
    public static $generator;


    /**
     * @inheritdoc
     */
    public static function getBundleDependencies(KernelInterface $kernel)
    {
        if (null === static::$cacheDir) {
            static::$cacheDir = $kernel->getCacheDir() . '/contao/bundles';
        }

        if (!static::$generator instanceof ModuleBundleGenerator) {
            static::$generator = new ModuleBundleGenerator();
        }

        $rootDir = $kernel->getRootDir();
        $bundles = [];

        static::$generator->generateBundles(static::$cacheDir, $rootDir);

        foreach (static::$generator->getContaoModules($rootDir) as $module) {
            $bundles[] = ModuleBundleGenerator::convertModuleToClass($module);
        }

        return $bundles;
    }
}
