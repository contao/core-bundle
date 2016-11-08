<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Framework;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * Provides methods to test the request scope.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Christian Schiffler <https://github.com/discordier>
 */
trait ScopeAwareTrait
{
    use ContainerAwareTrait;

    /**
     * Checks whether the request is a Contao the master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    protected function isContaoMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isContaoScope($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao back end master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    protected function isBackendMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isBackendScope($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao front end master request.
     *
     * @param KernelEvent $event
     *
     * @return bool
     */
    protected function isFrontendMasterRequest(KernelEvent $event)
    {
        return $event->isMasterRequest() && $this->isFrontendScope($event->getRequest());
    }

    /**
     * Checks whether the request is a Contao request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isContaoScope(Request $request = null)
    {
        return $this->isBackendScope($request) || $this->isFrontendScope($request);
    }

    /**
     * Checks whether the request is a Contao back end request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isBackendScope(Request $request = null)
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_BACKEND, $request);
    }

    /**
     * Checks whether the request is a Contao front end request.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isFrontendScope(Request $request = null)
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_FRONTEND, $request);
    }

    /**
     * Checks whether the _scope attributes matches a scope.
     *
     * @param string $scope
     * @param Request $request
     *
     * @return bool
     */
    private function isScope($scope, Request $request = null)
    {
        if (!$request) {
            if (null === $this->container) {
                return false;
            }

            $request = $this->container->get('request_stack')->getCurrentRequest();
        }

        if (null === $request || !$request->attributes->has('_scope')) {
            return false;
        }

        return $request->attributes->get('_scope') === $scope;
    }
}
