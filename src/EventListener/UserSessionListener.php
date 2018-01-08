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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\User;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSessionListener
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $authenticationTrustResolver;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * @var bool
     */
    private $registered = false;

    /**
     * @param Connection                           $connection
     * @param TokenStorageInterface                $tokenStorage
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver
     * @param ScopeMatcher                         $scopeMatcher
     */
    public function __construct(Connection $connection, TokenStorageInterface $tokenStorage, AuthenticationTrustResolverInterface $authenticationTrustResolver, ScopeMatcher $scopeMatcher)
    {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Replaces the current session data with the stored session data.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$this->scopeMatcher->isContaoMasterRequest($event)) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        $session = $user->session;

        if (\is_array($session)) {
            $this->getSessionBag($event->getRequest())->replace($session);
        }

        $this->registered = true;
    }

    /**
     * Writes the current session data to the database.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$this->registered || !$this->scopeMatcher->isContaoMasterRequest($event)) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->connection->update(
            $user->getTable(),
            ['session' => serialize($this->getSessionBag($event->getRequest())->all())],
            ['id' => $user->id]
        );
    }

    /**
     * Returns the session bag.
     *
     * @param Request $request
     *
     * @throws \RuntimeException
     *
     * @return AttributeBagInterface|SessionBagInterface
     */
    private function getSessionBag(Request $request): AttributeBagInterface
    {
        $session = $request->getSession();

        if (null === $session) {
            throw new \RuntimeException('The request did not contain a session');
        }

        $name = 'contao_frontend';

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $name = 'contao_backend';
        }

        return $session->getBag($name);
    }
}
