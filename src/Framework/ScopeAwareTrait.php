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

@trigger_error('Trait Contao\CoreBundle\Framework\ScopeAwareTrait has been deprecated since Contao 4.3 and will get removed in Contao 5.0 - use ' . ScopeTrait::class . ' instead', E_USER_DEPRECATED);

/**
 * Provides methods to test the request scope.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Christian Schiffler <https://github.com/discordier>
 *
 * @deprecated Since Contao 4.3 to be removed in 5.0 - Use the ScopeCheckingTrait instead.
 */
trait ScopeAwareTrait
{
    use ContainerAwareTrait;
    use ScopeTrait;

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
        return $this->isScopeWithContainerFallback(ContaoCoreBundle::SCOPE_BACKEND, $request);
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
        return $this->isScopeWithContainerFallback(ContaoCoreBundle::SCOPE_FRONTEND, $request);
    }

    /**
     * Checks whether the _scope attributes matches a scope.
     *
     * @param string $scope
     * @param Request $request
     *
     * @return bool
     */
    private function isScopeWithContainerFallback($scope, Request $request = null)
    {
        if (null === $request) {
            @trigger_error('Deriving the scope from the request_stack has been deprecated in Contao 4.3 and will get removed in Contao 5.0', E_USER_DEPRECATED);
            if (null === $this->container) {
                return false;
            }

            $request = $this->container->get('request_stack')->getCurrentRequest();

            if (null === $request) {
                return false;
            }
        }

        return $this->isScope($scope, $request);
    }
}
