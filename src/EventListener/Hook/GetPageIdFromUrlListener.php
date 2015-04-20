<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

use Contao\CoreBundle\Event\ReturnValueEvent;

/**
 * Listens to the contao.get_page_id_from_url event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class GetPageIdFromUrlListener extends AbstractHookListener
{
    /**
     * {@inheritdoc}
     */
    protected function getHookName()
    {
        return 'getPageIdFromUrl';
    }

    /**
     * Triggers the "getPageIdFromUrl" hook.
     *
     * @param ReturnValueEvent $event The event object
     */
    public function onGetPageIdFromUrl(ReturnValueEvent $event)
    {
        $fragments = $event->getValue();

        foreach ($this->getCallbacks() as $callback) {
            $fragments = call_user_func($this->getCallable($callback), $fragments);
        }

        $event->setValue($fragments);
    }
}
