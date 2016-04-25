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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Allows to register legacy Contao modules as bundle.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ContaoModuleBundle extends Bundle implements DependentBundleInterface
{
    /**
     * Old module names
     * @var array
     */
    private static $moduleMapping =
        [
            'core'       => 'Contao\CoreBundle\ContaoCoreBundle',
            'calendar'   => 'Contao\CalendarBundle\ContaoCalendarBundle',
            'comments'   => 'Contao\CommentsBundle\ContaoCommentsBundle',
            'faq'        => 'Contao\FaqBundle\ContaoFaqBundle',
            'listing'    => 'Contao\ListingBundle\ContaoListingBundle',
            'news'       => 'Contao\NewsBundle\ContaoNewsBundle',
            'newsletter' => 'Contao\NewsletterBundle\ContaoNewsletterBundle',
        ];


    /**
     * Sets the module name and application root directory.
     *
     * @param string $name    The module name
     * @param string $rootDir The application root directory
     *
     * @throws \LogicException
     */
    public function __construct($name, $rootDir)
    {
        $this->name = $name;
        $this->path = dirname($rootDir) . '/system/modules/' . $this->name;

        if (!is_dir($this->path)) {
            throw new \LogicException('The module folder "system/modules/' . $this->name . '" does not exist.');
        }

        if (file_exists($this->path . '/.skip')) {
            throw new \RuntimeException('The module "system/modules/' . $this->name . '" as been disabled.');
        }
    }

    /**
     * @inheritdoc
     */
    public static function getBundleDependencies(KernelInterface $kernel)
    {
        return [
            'Contao\CoreBundle\ContaoCoreBundle',
        ];
    }

    /**
     * @param string          $module
     * @param KernelInterface $kernel
     *
     * @return array
     *
     * @throws \RuntimeException if a required module is not installed
     */
    protected static function getModuleDependencies($module, KernelInterface $kernel)
    {
        $bundles = [];
        $requires = self::getAutoloadRequires(dirname($kernel->getRootDir()) . '/system/modules/' . $module);

        foreach ($requires as $name) {
            $moduleClass = self::convertModuleToClass($name);

            if (!class_exists($moduleClass)) {
                if (0 === strpos($name, '*')) {
                    continue;
                }

                throw new \RuntimeException(
                    sprintf('Contao module "%s" is required by "%s" but not installed.', $name, $module)
                );
            }

            $bundles[] = $moduleClass;
        }

        return $bundles;
    }

    /**
     * @param string $moduleDir
     *
     * @return array
     */
    private static function getAutoloadRequires($moduleDir)
    {
        $requires = ['core'];
        $iniFile  = $moduleDir . '/config/autoload.ini';

        if (!file_exists($iniFile)) {
            return $requires;
        }

        $autoload = parse_ini_file($iniFile, true);

        if (false === $autoload || !isset($autoload['requires']) || !is_array($autoload['requires'])) {
            return $requires;
        }

        return array_unique(array_merge($requires, $autoload['requires']));
    }


    private static function convertModuleToClass($name)
    {
        if (array_key_exists($name, self::$moduleMapping)) {
            return self::$moduleMapping[$name];
        }

        return 'Contao\CoreBundle\HttpKernel\Bundle\\' . Container::camelize($name);
    }
}
