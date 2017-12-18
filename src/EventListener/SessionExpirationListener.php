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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Temporary implementation until https://github.com/symfony/symfony/pull/12807 is available.
 */
class SessionExpirationListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ScopeMatcher          $scopeMatcher
     * @param LoggerInterface       $logger
     */
    public function __construct(TokenStorageInterface $tokenStorage, ScopeMatcher $scopeMatcher, LoggerInterface $logger)
    {
        $this->tokenStorage = $tokenStorage;
        $this->scopeMatcher = $scopeMatcher;
        $this->logger = $logger;
    }

    /**
     * Checks if the current tokens lifetime is still valid.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$event->getRequest()->hasPreviousSession()
            || null === $session
            || !$session->isStarted()
        ) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$this->scopeMatcher->isContaoMasterRequest($event)
            || !$token instanceof TokenInterface
            || !($user = $token->getUser()) instanceof User
        ) {
            return;
        }

        // Logout after 1 hour
        if ((time() - $session->getMetadataBag()->getLastUsed()) < 3600) {
            return;
        }

        $this->tokenStorage->setToken(null);

        $this->logger->info(
            sprintf('User "%s" has been logged out automatically due to inactivity', $user->getUsername()),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, $user->getUsername())]
        );

        $event->setResponse(new RedirectResponse($request->getRequestUri()));
    }
}
