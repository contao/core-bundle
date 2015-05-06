<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Terminal42\ContaoAdapterBundle\Adapter\FrontendAdapter;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Outputs a page from cache without loading a controller.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class OutputFromCacheListener extends ScopeAwareListener
{
    /**
     * Front end
     * @var FrontendAdapter
     */
    private $frontend;

    /**
     * Constructor.
     *
     * @param FrontendAdapter $frontend
     */
    public function __construct(FrontendAdapter $frontend)
    {
        $this->frontend = $frontend;
    }

    /**
     * Forwards the request to the Frontend class and sets the response if any.
     *
     * @param GetResponseEvent $event The event object
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->isFrontendMasterRequest($event)) {
            return;
        }

        if (null !== ($response = $this->frontend->getResponseFromCache())) {
            $event->setResponse($response);
        }
    }
}
