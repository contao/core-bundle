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
     * Returns the hook name.
     *
     * @return string The hook name
     */
    abstract function getHookName();

    /**
     * Returns the registered callbacks of a hook.
     *
     * @return array The registered callbacks
     */
    public function getCallbacks()
    {
        $hookName = $this->getHookName();

        if (!isset($GLOBALS['TL_HOOKS'][$hookName]) || !is_array($GLOBALS['TL_HOOKS'][$hookName])) {
            return [];
        }

        return $GLOBALS['TL_HOOKS'][$hookName];
    }
}
