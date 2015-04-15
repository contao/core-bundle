<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

/**
 * Parent class for hook listeners.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class AbstractHookListener
{
    /**
     * Checks whether a hook exists.
     *
     * @param string $name The name
     *
     * @return bool True if the hook exists
     */
    public function hookExists($name)
    {
        return isset($GLOBALS['TL_HOOKS'][$name]) && is_array($GLOBALS['TL_HOOKS'][$name]);
    }
}
