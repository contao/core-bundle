<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Security\TokenChecker;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class BypassMaintenanceListener
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenChecker
     */
    private $tokenChecker;

    /**
     * @var string
     */
    private $requestAttribute;

    /**
     * @param SessionInterface $session
     * @param TokenChecker     $tokenChecker
     * @param string           $requestAttribute
     */
    public function __construct(SessionInterface $session, TokenChecker $tokenChecker, string $requestAttribute = '_bypass_maintenance')
    {
        $this->session = $session;
        $this->tokenChecker = $tokenChecker;
        $this->requestAttribute = $requestAttribute;
    }

    /**
     * Adds the request attribute to the request.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->tokenChecker->isAuthenticated(BackendUser::SECURITY_SESSION_KEY)) {
            return;
        }

        $request->attributes->set($this->requestAttribute, true);
    }
}
