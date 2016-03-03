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
     * @inheritdoc
     */
    public static function getBundleDependencies(KernelInterface $kernel)
    {
        $kernelDir  = $kernel->getRootDir();
        $bundles    = [];

        foreach (static::getContaoModules(dirname($kernelDir)) as $module) {
            $bundles[] = ['Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle', [$module, $kernelDir]];
        }

        return $bundles;
    }

    /**
     * Find Contao modules in system/modules that are not marked as skippable
     *
     * @param string $contaoRoot
     *
     * @return array
     */
    private static function getContaoModules($contaoRoot)
    {
        $modules = [];

        foreach (scandir($contaoRoot . '/system/modules') as $dir) {
            if ('.' === $dir || '..' === $dir) {
                continue;
            }

            if (is_dir($contaoRoot . '/system/modules/' . $dir)
                && !file_exists($contaoRoot . '/system/modules/' . $dir . '/.skip')
            ) {
                $modules[] = $dir;
            }
        }

        return $modules;
    }
}
