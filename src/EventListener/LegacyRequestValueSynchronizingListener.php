<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\CoreBundle\Request\ValueAdapter;
use Contao\Environment;
use Contao\Input;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Sets the current request on the \Input and \Environment classes.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 *
 * @deprecated Deprecated since Contao 4.3, to be removed in Contao 5.0.
 *             Use the request or request stack instead.
 */
class LegacyRequestValueSynchronizingListener
{
    use ScopeAwareTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Store the request in the Contao 3 classes.
     *
     * @return void
     */
    public function startRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        // Only handle for Contao requests.
        if (!$this->isContaoScope($request)) {
            $this->handleForRequest(null);
            return;
        }

        if (!$request->attributes->has('_contao_value_adapter')) {
            $request->attributes->set('_contao_value_adapter', new ValueAdapter($request));
        }

        $this->handleForRequest($request);
    }

    /**
     * Restore the request in the Contao 3 classes.
     *
     * @return void
     */
    public function finishRequest()
    {
        $request = $this->requestStack->getParentRequest();
        // Only handle for Contao requests.
        if (!$this->isContaoScope($request)) {
            $this->handleForRequest(null);
            return;
        }

        $this->handleForRequest($request);
    }

    /**
     * Set the passed request in the Environment and Input classes.
     *
     * @param Request|null $request
     *
     * @return void
     */
    private function handleForRequest(Request $request = null)
    {
        Environment::setRequest($request);
        Input::setValueAdapter($request ? $request->attributes->get('_contao_value_adapter') : null);
    }

    /**
     * Checks whether the request is a Contao request.
     *
     * @param Request $request The request to check.
     *
     * @return bool
     */
    protected function isContaoScope(Request $request = null)
    {
        return $this->isScope(ContaoCoreBundle::SCOPE_BACKEND, $request)
            || $this->isScope(ContaoCoreBundle::SCOPE_FRONTEND, $request);
    }

    /**
     * Checks whether the _scope attributes matches a scope.
     *
     * @param string  $scope   The scope to check.
     *
     * @param Request $request The request to check.
     *
     * @return bool
     */
    private function isScope($scope, Request $request = null)
    {
        if (null === $request || !$request->attributes->has('_scope')) {
            return false;
        }

        return $request->attributes->get('_scope') === $scope;
    }
}
