<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

use Contao\CoreBundle\Event\GetCacheKeyEvent;
use Contao\System;

/**
 * Triggers the "getCacheKey" hook.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class GetCacheKeyListener extends AbstractHookListener
{
    /**
     * Triggers the "getCacheKey" hook.
     *
     * @param GetCacheKeyEvent $event The event object
     */
    public function onGetCacheKey(GetCacheKeyEvent $event)
    {
        if (!$this->hookExists('getCacheKey')) {
            return;
        }

        foreach ($GLOBALS['TL_HOOKS']['getCacheKey'] as $callback) {
            $event->setCacheKey(System::importStatic($callback[0])->$callback[1]($event->getCacheKey()));
        }
    }
}
