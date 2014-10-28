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
class ConfigCollection implements \IteratorAggregate
{
    /**
     * @var ConfigInterface[]
     */
    protected $configs = [];

    /**
     * Returns an array of configurations
     *
     * @return ConfigInterface[] The configuration array
     */
    public function all()
    {
        return $this->configs;
    }

    /**
     * Adds a configuration object to the collection
     *
     * @param ConfigInterface $config
     *
     * @return $this The collection object
     */
    public function add(ConfigInterface $config)
    {
        $this->configs[] = $config;

        return $this;
    }

    /**
     * Checks whether the collection is empty
     *
     * @return bool True if the collection is empty
     */
    public function isEmpty()
    {
        return empty($this->configs);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configs);
    }
}
