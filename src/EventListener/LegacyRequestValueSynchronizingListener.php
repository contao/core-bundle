<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Request\ValueAdapter;
use Contao\Environment;
use Contao\Input;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

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
}
