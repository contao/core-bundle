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

/**
 * Listens to the contao.get_cache_key event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class GetCacheKeyListener extends AbstractHookListener
{
    /**
     * {@inheritdoc}
     */
    protected function getHookName()
    {
        return 'getCacheKey';
    }

    /**
     * Triggers the "getCacheKey" hook.
     *
     * @param GetCacheKeyEvent $event The event object
     */
    public function onGetCacheKey(GetCacheKeyEvent $event)
    {
        $cacheKey = $event->getCacheKey();

        foreach ($this->getCallbacks() as $callback) {
            $cacheKey = call_user_func($this->getCallable($callback), $cacheKey);
        }

        $event->setCacheKey($cacheKey);
    }
}
