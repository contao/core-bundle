<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

use Contao\CoreBundle\Event\TemplateEvent;
use Contao\System;

/**
 * Listens to the contao.parse_backend_template event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class ParseBackendTemplateListener extends AbstractHookListener
{
    /**
     * Triggers the "parseBackendTemplate" hook.
     *
     * @param TemplateEvent $event The event object
     */
    public function onParseBackendTemplate(TemplateEvent $event)
    {
        if (!$this->hookExists('parseBackendTemplate')) {
            return;
        }

        foreach ($GLOBALS['TL_HOOKS']['parseBackendTemplate'] as $callback) {
            $event->setBuffer(
                System::importStatic($callback[0])->$callback[1](
                    $event->getBuffer(), $event->getName(), $event->getTemplate()
                )
            );
        }
    }
}
