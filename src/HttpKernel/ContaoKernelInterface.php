<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\HttpKernel;

use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoBundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Autoloads the bundles
 *
 * @author Leo Feyer <https://contao.org>
 */
interface ContaoKernelInterface extends KernelInterface
{
    /**
     * Writes the bundle cache
     */
    public function writeBundleCache();

    /**
     * Loads the bundle cache
     */
    public function loadBundleCache();

    /**
     * Return all Contao bundles as array
     *
     * @return ContaoBundleInterface[] The Contao bundles
     */
    public function getContaoBundles();
}
