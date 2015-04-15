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
 * Triggers the "parseFrontendTemplate" hook.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class ParseFrontendTemplateListener extends AbstractHookListener
{
    /**
     * Triggers the "parseFrontendTemplate" hook.
     *
     * @param TemplateEvent $event The event object
     */
    public function onParseFrontendTemplate(TemplateEvent $event)
    {
        if (!$this->hookExists('parseFrontendTemplate')) {
            return;
        }

        foreach ($GLOBALS['TL_HOOKS']['parseFrontendTemplate'] as $callback) {
            $event->setBuffer(
                System::importStatic($callback[0])->$callback[1](
                    $event->getBuffer(), $event->getName(), $event->getTemplate()
                )
            );
        }
    }
}
