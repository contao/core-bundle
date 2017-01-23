<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * Checks the request for a Contao context.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class RequestContext
{
    /**
     * @var RequestMatcherInterface
     */
    private $frontendMatcher;

    /**
     * @var RequestMatcherInterface
     */
    private $backendMatcher;

    /**
     * Constructor.
     *
     * @param RequestMatcherInterface $frontendMatcher
     * @param RequestMatcherInterface $backendMatcher
     */
    public function __construct(RequestMatcherInterface $frontendMatcher, RequestMatcherInterface $backendMatcher)
    {
        $this->frontendMatcher = $frontendMatcher;
        $this->backendMatcher = $backendMatcher;
    }

    /**
     * Checks whether the request is a Contao the master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    public function isContaoMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isContaoRequest($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao back end master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    public function isBackendMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isBackendRequest($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao front end master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    public function isFrontendMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isFrontendRequest($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao request.
     *
     * @return bool
     */
    public function isContaoRequest(Request $request)
    {
        return $this->isBackendRequest($request) || $this->isFrontendRequest($request);
    }

    /**
     * Checks whether the request is a Contao back end request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isBackendRequest(Request $request)
    {
        return $this->backendMatcher->matches($request);
    }

    /**
     * Checks whether the request is a Contao front end request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isFrontendRequest(Request $request)
    {
        return $this->frontendMatcher->matches($request);
    }
}
