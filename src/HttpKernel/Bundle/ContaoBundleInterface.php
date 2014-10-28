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
     * Return the path to the config directory
     *
     * @return string The config path
     */
    public function getConfigPath();

    /**
     * Return the path to the DCA directory
     *
     * @return string The DCA path
     */
    public function getDcaPath();

    /**
     * Return the path to the languages directory
     *
     * @return string The languages path
     */
    public function getLanguagesPath();

    /**
     * Return the path to the templates directory
     *
     * @return string The templates path
     */
    public function getTemplatesPath();
}
