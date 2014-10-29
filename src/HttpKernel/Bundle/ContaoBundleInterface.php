<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\HttpKernel\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Configures a Contao bundle
 *
 * @author Leo Feyer <https://contao.org>
 */
interface ContaoBundleInterface extends BundleInterface
{
    /**
     * Returns the path to the Contao resources directory
     *
     * @return string The config path
     */
    public function getContaoResourcesPath();
}
