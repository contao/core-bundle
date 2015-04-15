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
 * Listens to the contao.parse_frontend_template event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.get_cache_key event instead.
 */
class ParseFrontendTemplateListener extends AbstractHookListener
{
    /**
     * {@inheritdoc}
     */
    public function getHookName()
    {
        return 'parseFrontendTemplate';
    }

    /**
     * Triggers the "parseFrontendTemplate" hook.
     *
     * @param TemplateEvent $event The event object
     */
    public function onParseFrontendTemplate(TemplateEvent $event)
    {
        $buffer = $event->getBuffer();
        $key    = $event->getKey();

        foreach ($this->getCallbacks() as $callback) {
            $buffer = System::importStatic($callback[0])->$callback[1]($buffer, $key);
        }

        $event->setBuffer($buffer);
        $event->setKey($key);
    }
}
