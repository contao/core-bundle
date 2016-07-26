<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Controller\ArgumentConverterInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Allows arbitrary controllers to easily convert attributes on the current
 * request for their own actions.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class ControllerArgumentListener
{
    /**
     * Convert arguments.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController()[0];

        if (!$controller instanceof ArgumentConverterInterface) {
            return;
        }

        $controller->convertArguments($event->getRequest());
    }
}
