<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Exception\ResponseException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Creates a response from an exception.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ResponseExceptionListener
{
    /**
     * Sets the response from the exception.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ResponseException) {
            return;
        }

        $event->allowCustomResponseCode();
        $event->setResponse($exception->getResponse());
    }
}
