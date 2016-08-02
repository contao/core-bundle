<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel\Bundle;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Generates classes for Contao modules
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ModuleBundleGenerator
{
    /**
     * Old module names
     * @var array
     */
    private static $moduleMapping = [
        'core'       => 'Contao\CoreBundle\ContaoCoreBundle',
        'calendar'   => 'Contao\CalendarBundle\ContaoCalendarBundle',
        'comments'   => 'Contao\CommentsBundle\ContaoCommentsBundle',
        'faq'        => 'Contao\FaqBundle\ContaoFaqBundle',
        'listing'    => 'Contao\ListingBundle\ContaoListingBundle',
        'news'       => 'Contao\NewsBundle\ContaoNewsBundle',
        'newsletter' => 'Contao\NewsletterBundle\ContaoNewsletterBundle',
    ];

    /**
     * @var array
     */
    private $modules;

    /**
     * Generate bundle classes for folders in system/modules
     *
     * @param string          $cacheDir
     * @param string          $rootDir
     * @param Filesystem|null $filesystem
     */
    public function generateBundles($cacheDir, $rootDir, Filesystem $filesystem = null)
    {
        if (null === $filesystem) {
            $filesystem = new Filesystem();
        }

        $filesystem->remove($cacheDir);
        $filesystem->mkdir($cacheDir);

        $modules = $this->getContaoModules($rootDir);

        foreach ($modules as $module) {
            $this->createBundleClass($module, $cacheDir, $rootDir);
        }
    }

    /**
     * Find Contao modules in system/modules that are not marked as skippable
     *
     * @param string $rootDir
     *
     * @return array
     */
    public function getContaoModules($rootDir)
    {
        $modulesDir = dirname($rootDir) . '/system/modules';

        if (null === $this->modules) {
            $this->modules = [];

            foreach (scandir($modulesDir) as $dir) {
                if ('.' === $dir || '..' === $dir) {
                    continue;
                }

                if (is_dir($modulesDir . '/' . $dir)
                    && !file_exists($modulesDir . '/' . $dir . '/.skip')
                ) {
                    $this->modules[] = $dir;
                }
            }
        }

        return $this->modules;
    }

    /**
     * @param string $module
     * @param string $cacheDir
     * @param string $rootDir
     */
    private function createBundleClass($module, $cacheDir, $rootDir)
    {
        $fqcn = static::convertModuleToClass($module);
        $className = substr($fqcn, strrpos($fqcn, '\\')+1);

        $varModule = var_export($module, true);
        $varRootDir = var_export($rootDir, true);

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

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Generated bundle class for Contao module in system/modules/$module
 */
class $className extends ContaoModuleBundle
{
    public function __construct()
    {
        parent::__construct($varModule, $varRootDir);
    }
    
    public static function getBundleDependencies(KernelInterface \$kernel)
    {
        return parent::getModuleDependencies($varModule, \$kernel);
    }
}

CLASS;

        file_put_contents($cacheDir . '/' . $className . '.php', $code);
    }

    /**
     * Returns FQCN from module folder and maps legacy core module names.
     *
     * @param string $name
     *
     * @return string
     */
    public static function convertModuleToClass($name)
    {
        if (array_key_exists($name, self::$moduleMapping)) {
            return self::$moduleMapping[$name];
        }

        $className = strtr(ucwords(preg_replace('/[^a-z0-9]+/i', ' ', $name)), [' ' => '']) . 'ModuleBundle';

        return 'Contao\CoreBundle\HttpKernel\Bundle\\' . $className;
    }
}
