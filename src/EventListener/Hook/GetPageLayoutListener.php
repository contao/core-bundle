<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

use Contao\CoreBundle\Event\PageEvent;

/**
 * Listens to the contao.get_page_layout event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_page_layout event instead.
 */
class GetPageLayoutListener extends AbstractHookListener
{
    /**
     * {@inheritdoc}
     */
    protected function getHookName()
    {
        return 'getPageLayout';
    }

    /**
     * Triggers the "getPageLayout" hook.
     *
     * @param PageEvent $event The event object
     */
    public function onGetPageLayout(PageEvent $event)
    {
        $this->handlePageEvent($event);
    }
}
