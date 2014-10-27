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
 * Handles a collection of configuration objects
 *
 * @author Leo Feyer <https://contao.org>
 */
class ConfigCollection implements ConfigCollectionInterface
{
    /**
     * @var ConfigInterface[]
     */
    protected $configs = [];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function add(ConfigInterface $config)
    {
        $this->configs[] = $config;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configs);
    }
}
