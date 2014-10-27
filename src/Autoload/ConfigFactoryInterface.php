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
 * Configuration factory interface
 *
 * @author Leo Feyer <https://contao.org>
 */
interface ConfigFactoryInterface
{
    /**
     * Adds the bundle to the collection
     *
     * @param array $config The configuration array
     */
    public function create(array $config);
}
