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
 * Bundle factory interface
 *
 * @author Leo Feyer <https://contao.org>
 */
interface BundleFactoryInterface
{
    /**
     * Adds the bundle to the collection
     *
     * @param array               $config     The configuration array
     * @param CollectionInterface $collection The collection array
     */
    public function create(array $config, CollectionInterface $collection);
}
