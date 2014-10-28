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
     * Creates a configuration object and returns it
     *
     * @param array $config The configuration array
     *
     * @return Config The configuration object
     */
    public function create(array $config);
}
