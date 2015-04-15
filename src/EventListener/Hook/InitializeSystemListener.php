<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

use Contao\CoreBundle\Event\InitializeSystemEvent;
use Contao\System;

/**
 * Triggers the "initializeSystem" hook.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.initialize_system event instead.
 */
class InitializeSystemListener extends AbstractHookListener
{
    /**
     * Triggers the "initializeSystem" hook.
     *
     * @param InitializeSystemEvent $event The event object
     */
    public function onInitializeSystem(InitializeSystemEvent $event)
    {
        $this->triggerHook();

        if (file_exists($event->getRootDir() . '/system/config/initconfig.php')) {
            include $event->getRootDir() . '/system/config/initconfig.php';
        }
    }

    /**
     * Triggers the hook.
     */
    private function triggerHook()
    {
        if (!$this->hookExists('initializeSystem')) {
            return;
        }

        foreach ($GLOBALS['TL_HOOKS']['initializeSystem'] as $callback) {
            System::importStatic($callback[0])->$callback[1]();
        }
    }
}
