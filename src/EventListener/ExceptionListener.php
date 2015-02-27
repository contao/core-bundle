<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Exception\DieNicelyException;
use Contao\CoreBundle\Exception\ResponseException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Handle exceptions and create a proper response containing the error screen when debug mode is not active.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class ExceptionListener
{
    /**
     * Forwards the request to the Frontend class if there is a page object.
     *
     * @param GetResponseForExceptionEvent $event The event object
     */
    public function onGetResponseForException(GetResponseForExceptionEvent $event)
    {
        // Search if an response is somewhere in the exception list.
        $exception = $event->getException();
        do {
            if ($exception instanceof ResponseException) {
                $event->setResponse($exception->getResponse());
                return;
            }
        } while (null !== ($exception = $exception->getPrevious()));

        $this->logContaoException($event->getException());

        $exception = $event->getException();
        do {
            if ($exception instanceof DieNicelyException) {
                $event->setResponse($this->handleDieNicelyException($exception));
                return;
            }
        } while (null !== ($exception = $exception->getPrevious()));

        $event->setResponse(
            $this->handleDieNicelyException(
                new DieNicelyException('be_error', 'An error occurred while executing this script!', 0, $event->getException())
            )
        );
    }

    /**
     * Handle the die nicely exceptions.
     *
     * @return Response The created response.
     */
    protected function handleDieNicelyException(DieNicelyException $exception)
    {
        // If display error is on, dump the real exception in a nice representation on the screen.
        if (\Contao\Config::get('displayErrors')) {
            return $this->exceptionToResponse($exception);
        }

        if (file_exists(
            $file = sprintf(
                '%s/templates/%s.html5',
                TL_ROOT,
                $exception->getTemplate()
            )
        )) {
            $content = include $file;
        } elseif (file_exists(
            $file = sprintf(
                '%s/vendor/contao/core-bundle/contao/templates/backend/%s.html5',
                TL_ROOT,
                $exception->getTemplate()
            )
        )) {
            $content = include $file;
        } else {
            $content = $exception->getMessage();
        }

        return new Response($content, 500, array('Content-type' => ' text/html; charset=utf-8'));
    }

    /**
     * Log the given exception into to Contao log.
     *
     * @param \Exception $e The exception
     */
    private function logContaoException(\Exception $e)
    {
        $exception = $e;
        // FIXME: use the logger interface here.
        do {
            error_log(sprintf("PHP Fatal error: Uncaught exception '%s' with message '%s' thrown in %s on line %s\n%s",
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getTraceAsString())
            );
        } while ($exception = $exception->getPrevious());
    }

    /**
     * Clean the TL_ROOT path from some text content.
     *
     * @param string $string The string to clean
     *
     * @return string The cleaned string
     */
    private function cleanPath($string)
    {
        return str_replace(TL_ROOT . '/', '', $string);
    }

    /**
     * Create a response from an exception.
     *
     * @param \Exception $exception The exception to create a response from
     *
     * @return Response The created response with result code 500
     */
    private function exceptionToResponse(\Exception $exception)
    {
        $message = '';
        do {
            $message .= sprintf(
                "<strong>Fatal error</strong>: Uncaught exception <strong>%s</strong> with message <strong>%s</strong> thrown in <strong>%s</strong> on line <strong>%s</strong>\n<pre style=\"margin:11px 0 0\">\n%s\n</pre>\n",
                get_class($exception),
                $exception->getMessage(),
                $this->cleanPath($exception->getFile()),
                $exception->getLine(),
                $this->cleanPath($exception->getTraceAsString())
            );
        } while ($exception = $exception->getPrevious());

        return new Response($message, 500, array('Content-type' => ' text/html; charset=utf-8'));
    }
}
