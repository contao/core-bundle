<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ScopeTrait;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Adds the referer ID to the current request.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class RefererIdListener
{
    use ScopeTrait;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    /**
     * Constructor.
     *
     * @param CsrfTokenManagerInterface $tokenManager
     */
    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * Adds the referer ID to the request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->isBackendMasterRequest($event)) {
            return;
        }

        $request = $event->getRequest();

        /** @var CsrfToken $token */
        $token = $this->tokenManager->refreshToken('contao_referer_id');

        $request->attributes->set('_contao_referer_id', $token->getValue());
    }
}
