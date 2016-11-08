<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\FrontendUser;
use Contao\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Stores and restores the user session.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Christian Schiffler <https://github.com/discordier>
 */
class UserSessionListener
{
    use ScopeAwareTrait;
    use UserAwareTrait;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     * @param Connection       $connection
     */
    public function __construct(SessionInterface $session, Connection $connection)
    {
        $this->session = $session;
        $this->connection = $connection;
    }

    /**
     * Replaces the current session data with the stored session data.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->hasUser() || !$this->isContaoMasterRequest($event)) {
            return;
        }

        $user = $this->getUserObject();

        if (!($user instanceof User)) {
            return;
        }

        $session = $user->session;

        if (is_array($session)) {
            $this->getSessionBag($event->getRequest())->replace($session);
        }
    }

    /**
     * Writes the current session data to the database.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$this->hasUser() || !$this->isContaoMasterRequest($event)) {
            return;
        }

        $user = $this->getUserObject();

        if (!($user instanceof User)) {
            return;
        }

        $this->connection
            ->prepare('UPDATE '.$user->getTable().' SET session=? WHERE id=?')
            ->execute([serialize($this->getSessionBag($event->getRequest())->all()), $user->id])
        ;
    }

    /**
     * Returns the user object depending on the container scope.
     *
     * @return FrontendUser|BackendUser|null
     */
    private function getUserObject()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Returns the session bag.
     *
     * @param Request $request
     *
     * @return AttributeBagInterface
     */
    private function getSessionBag(Request $request)
    {
        if ($this->isBackendScope($request)) {
            $bag = 'contao_backend';
        } else {
            $bag = 'contao_frontend';
        }

        return $this->session->getBag($bag);
    }
}
