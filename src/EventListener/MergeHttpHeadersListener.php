<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\HttpKernel\Header\HeaderStorageInterface;
use Contao\CoreBundle\HttpKernel\Header\NativeHeaderStorage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Adds HTTP headers sent by Contao to the Symfony response.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class MergeHttpHeadersListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $stack = [];

    /**
     * @var array
     */
    private $current = [];

    /**
     * @var array
     */
    private $multiHeaders = [
        'set-cookie',
        'link',
        'vary',
        'pragma',
        'cache-control',
    ];
    /**
     * @var HeaderStorageInterface|null
     */
    private $headerStorage;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface    $framework
     * @param HeaderStorageInterface|null $headerStorage
     */
    public function __construct(ContaoFrameworkInterface $framework, HeaderStorageInterface $headerStorage = null)
    {
        $this->framework = $framework;
        $this->headerStorage = $headerStorage ?: new NativeHeaderStorage();
    }

    /**
     * Returns the multi-value headers.
     *
     * @return array
     */
    public function getMultiHeaders()
    {
        return array_values($this->multiHeaders);
    }

    /**
     * Sets the multi-value headers.
     *
     * @param array $headers
     */
    public function setMultiHeader(array $headers)
    {
        $this->multiHeaders = $headers;
    }

    /**
     * Adds a multi-value header.
     *
     * @param string $name
     */
    public function addMultiHeader($name)
    {
        $uniqueKey = $this->getUniqueKey($name);

        if (!in_array($uniqueKey, $this->multiHeaders, true)) {
            $this->multiHeaders[] = $uniqueKey;
        }
    }

    /**
     * Removes a multi-value header.
     *
     * @param string $name
     */
    public function removeMultiHeader($name)
    {
        if (false !== ($i = array_search($this->getUniqueKey($name), $this->multiHeaders, true))) {
            unset($this->multiHeaders[$i]);
        }
    }

    /**
     * Starts a new header stack.
     */
    public function onKernelRequest(): void
    {
        // Store the headers that were added before this request
        $this->fetchHttpHeaders();

        // Push the old headers to stack and create a new headers array
        $this->stack[] = $this->current;
        $this->current = [];
    }

    /**
     * Adds the Contao headers to the Symfony response.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->framework->isInitialized()) {
            return;
        }

        // Fetch remaining headers and add them to the response
        $this->fetchHttpHeaders();
        $this->setResponseHeaders($event->getResponse());

        $this->current = array_pop($this->stack);

        if (!is_array($this->current)) {
            $this->current = [];
        }
    }

    /**
     * Sets the current HTTP headers on the response.
     *
     * @param Response $response
     */
    private function setResponseHeaders(Response $response)
    {
        foreach ($this->current as $header) {
            list($name, $content) = explode(':', $header, 2);

            $uniqueKey = $this->getUniqueKey($name);

            if (in_array($uniqueKey, $this->multiHeaders, true)) {
                $response->headers->set($uniqueKey, trim($content), false);
            } elseif (!$response->headers->has($uniqueKey)) {
                $response->headers->set($uniqueKey, trim($content));
            }
        }
    }

    /**
     * Fetches and stores HTTP headers from PHP.
     */
    private function fetchHttpHeaders()
    {
        $this->current = array_merge($this->current, $this->headerStorage->all());
        $this->headerStorage->clear();
    }

    /**
     * Returns the unique header key.
     *
     * @param string $name
     *
     * @return string
     */
    private function getUniqueKey($name)
    {
        return str_replace('_', '-', strtolower($name));
    }
}
