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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Load configurations from a yaml file and prepend them to the extension configs.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class YamlFilePrependsLoader extends PrependsLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $configValues = Yaml::parse($this->locator->locate($resource));
        $this->prepend($configValues);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

}
