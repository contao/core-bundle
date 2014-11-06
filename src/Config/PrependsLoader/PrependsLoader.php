<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Config\PrependsLoader;

use Symfony\Component\DependencyInjection\Loader\FileLoader;

/**
 * Load configurations and prepend them to the extension configs.
 */
abstract class PrependsLoader extends FileLoader
{
    /**
     * Prepend the configuration to extension configs.
     *
     * @param array $configValues The extension configs.
     */
    protected function prepend(array $configValues)
    {
        foreach ($configValues as $bundleName => $bundleConfigValues) {
            $this->container->prependExtensionConfig($bundleName, $bundleConfigValues);
        }
    }
}
