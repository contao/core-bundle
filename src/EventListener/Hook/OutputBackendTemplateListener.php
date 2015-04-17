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

/**
 * Listens to the contao.output_backend_template event.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated in Contao 4.0, to be removed in Contao 5.0.
 *             Subscribe to the contao.output_backend_template event instead.
 */
class OutputBackendTemplateListener extends AbstractHookListener
{
    /**
     * {@inheritdoc}
     */
    protected function getHookName()
    {
        return 'outputBackendTemplate';
    }

    /**
     * Triggers the "outputBackendTemplate" hook.
     *
     * @param TemplateEvent $event The event object
     */
    public function onOutputBackendTemplate(TemplateEvent $event)
    {
        $this->handleTemplateEvent($event);
    }
}
