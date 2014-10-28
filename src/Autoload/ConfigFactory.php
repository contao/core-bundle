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
        return Config::create()
            ->setName($config['name'])
            ->setClass($config['class'])
            ->setReplace($config['replace'])
            ->setEnvironments($config['environments'])
            ->setLoadAfter($config['load-after'])
        ;
    }
}
