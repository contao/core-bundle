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
 * Autoload collection interface
 *
 * @author Leo Feyer <https://contao.org>
 */
interface CollectionInterface extends \IteratorAggregate
{
    /**
     * Returns an array of bundles
     *
     * @return BundleInterface[] The bundles array
     */
    public function all();

    /**
     * Adds a bundle to the collection
     *
     * @param BundleInterface $bundle
     *
     * @return $this The collection object
     */
    public function add(BundleInterface $bundle);
}
