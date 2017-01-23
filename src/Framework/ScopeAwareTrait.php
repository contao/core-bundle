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
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * Provides methods to test the request scope.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated since Contao 4.4, to be removed in Contao 5.
 *             Use contao.routing.frontend_matcher and contao.routing.backend_matcher services.
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
        return $event->isMasterRequest() && $this->isContaoScope();
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
        return $event->isMasterRequest() && $this->isBackendScope();
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
        return $event->isMasterRequest() && $this->isFrontendScope();
    }

    /**
     * Checks whether the request is a Contao request.
     *
     * @return bool
     */
    protected function isContaoScope()
    {
        return $this->isBackendScope() || $this->isFrontendScope();
    }

    /**
     * Checks whether the request is a Contao back end request.
     *
     * @return bool
     */
    protected function isBackendScope()
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_BACKEND);
    }

    /**
     * Checks whether the request is a Contao front end request.
     *
     * @return bool
     */
    protected function isFrontendScope()
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_FRONTEND);
    }

    /**
     * Checks whether the _scope attributes matches a scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    private function isScope($scope)
    {
        if (null === $this->container
            || null === ($request = $this->container->get('request_stack')->getCurrentRequest())
        ) {
            return false;
        }

        /** @var RequestMatcher $matcher */
        $matcher = $this->container->get(sprintf('contao.routing.%s_matcher', $scope));

        return $matcher->matches($request);
    }
}
