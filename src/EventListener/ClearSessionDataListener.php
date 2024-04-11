<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Session\Attribute\AutoExpiringAttribute;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * The priority must be higher than the one of the Symfony save session listener
 * (defaults to -1000).
 *
 * @internal
 */
#[AsEventListener(priority: -768)]
class ClearSessionDataListener
{
    /**
     * Clear the Contao session data if not a POST request.
     */
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isMethod('POST')) {
            return;
        }

        if (!$request->hasSession() || !$request->getSession()->isStarted()) {
            return;
        }

        if ($event->getResponse()->isSuccessful()) {
            $this->clearLoginData($request->getSession());
            $this->clearAutoExpiringSessionAttributes($request->getSession());
        }
    }

    private function clearLoginData(SessionInterface $session): void
    {
        $session->remove(SecurityRequestAttributes::AUTHENTICATION_ERROR);
        $session->remove(SecurityRequestAttributes::LAST_USERNAME);
    }

    private function clearAutoExpiringSessionAttributes(SessionInterface $session): void
    {
        foreach ($session->all() as $k => $v) {
            if ($v instanceof AutoExpiringAttribute && $v->isExpired()) {
                $session->remove($k);
            }
        }
    }
}
