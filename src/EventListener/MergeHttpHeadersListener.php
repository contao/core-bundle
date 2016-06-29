<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Adds HTTP headers sent by Contao to the Symfony response.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class MergeHttpHeadersListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $contaoFramework;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $contaoFramework The Contao framework
     */
    public function __construct(ContaoFrameworkInterface $contaoFramework)
    {
        $this->contaoFramework = $contaoFramework;
        $this->setHeaders(headers_list());
    }

    /**
     * Returns the headers.
     *
     * @return array The headers array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the headers.
     *
     * @param array $headers The headers array
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Adds the Contao headers to the Symfony response.
     *
     * @param FilterResponseEvent $event The event object
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->contaoFramework->isInitialized()) {
            return;
        }

        $event->setResponse($this->mergeHttpHeaders($event->getResponse()));
    }

    /**
     * Merges the HTTP headers.
     *
     * @param Response $response The response object
     *
     * @return Response The response object
     */
    private function mergeHttpHeaders(Response $response)
    {
        foreach ($this->getHeaders() as $header) {
            list($name, $content) = explode(':', $header, 2);

            if ('cli' !== PHP_SAPI) {
                header_remove($name);
            }

            // Do not replace existing response headers
            if (!$response->headers->has($name)) {
                $response->headers->set($name, trim($content));
            }
        }

        return $response;
    }
}
