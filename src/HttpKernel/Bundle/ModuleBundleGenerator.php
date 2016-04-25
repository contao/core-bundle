<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel\Bundle;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generates classes for Contao modules
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ModuleBundleGenerator
{

    public function generateBundles($cacheDir, $rootDir, Filesystem $filesystem = null)
    {
        if (null === $filesystem) {
            $filesystem = new Filesystem();
        }

        $filesystem->remove($cacheDir);
        $filesystem->mkdir($cacheDir);

        $modules = $this->getContaoModules(dirname($rootDir) . '/system/modules');

        foreach ($modules as $module) {
            $this->createBundleClass($module, $cacheDir, $rootDir);
        }
    }

    /**
     * Find Contao modules in system/modules that are not marked as skippable
     *
     * @param string $modulesDir
     *
     * @return array
     */
    private function getContaoModules($modulesDir)
    {
        $modules = [];

        foreach (scandir($modulesDir) as $dir) {
            if ('.' === $dir || '..' === $dir) {
                continue;
            }

            if (is_dir($modulesDir . '/' . $dir)) {
                $modules[] = $dir;
            }
        }

        return $modules;
    }

    private function createBundleClass($module, $cacheDir, $rootDir)
    {
        $className = Container::camelize($module) . 'ModuleBundle';

        $code = <<<CLASS
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
use Symfony\Component\HttpKernel\KernelInterface;

class $className extends ContaoModuleBundle implements DependentBundleInterface
{
    public function __construct()
    {
        parent::__construct('$module', '$rootDir');
    }
    
    public static function getBundleDependencies(KernelInterface \$kernel)
    {
        return parent::getModuleDependencies('$module', \$kernel);
    }
}

CLASS;

        file_put_contents($cacheDir . '/' . $className . '.php', $code);
    }
}
