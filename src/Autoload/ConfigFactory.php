<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Autoload;

/**
 * Creates a configuration object
 *
 * @author Leo Feyer <https://contao.org>
 */
class ConfigFactory implements ConfigFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $config)
    {
        $configObject = new Config();
        $configObject->setName($config['name']);
        $configObject->setClass($config['class']);
        $configObject->setReplace($config['replace']);
        $configObject->setEnvironments($config['environments']);
        $configObject->setLoadAfter($config['load-after']);

        return $configObject;
    }
}
