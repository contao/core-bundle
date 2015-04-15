<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\System;
use Symfony\Component\EventDispatcher\Event;

/**
 * Triggers the "initializeSystem" hook.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class InitializeSystemHookListener
{
    /**
     * Triggers the "initializeSystem" hook.
     *
     * @param Event $event The event object
     *
     * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
     *             Subscribe to the contao.initialize_system event instead.
     */
    public function onInitializeSystem(Event $event)
    {
        if (isset($GLOBALS['TL_HOOKS']['initializeSystem']) && is_array($GLOBALS['TL_HOOKS']['initializeSystem'])) {
            foreach ($GLOBALS['TL_HOOKS']['initializeSystem'] as $callback) {
                System::importStatic($callback[0])->$callback[1]();
            }
        }
    }
}
