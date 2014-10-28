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
 * Configuration collection interface
 *
 * @author Leo Feyer <https://contao.org>
 */
interface ConfigCollectionInterface extends \IteratorAggregate
{
    /**
     * Returns an array of configurations
     *
     * @return ConfigInterface[] The configuration array
     */
    public function all();

    /**
     * Adds a configuration object to the collection
     *
     * @param ConfigInterface $config
     *
     * @return $this The collection object
     */
    public function add(ConfigInterface $config);
}
