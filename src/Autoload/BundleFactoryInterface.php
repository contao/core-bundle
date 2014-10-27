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
     * Returns the bundle
     *
     * @param \SplFileInfo        $file       The file object
     * @param CollectionInterface $collection The collection array
     */
    public function create(\SplFileInfo $file, CollectionInterface $collection);
}
