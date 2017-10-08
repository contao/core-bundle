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
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

/**
 * SwitchUserListener allows a user to impersonate another one temporarily
 * (like the Unix su command).
 */
class SwitchUserListener
{
    protected $logger;
    protected $tokenStorage;

    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Logs the switch to another user.
     *
     * @param SwitchUserEvent $event
     */
    public function onSwitchUser(SwitchUserEvent $event): void
    {
        /** @var BackendUser $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var BackendUser $targetUser */
        $targetUser = $event->getTargetUser();

        $this->logger->info(sprintf('User %s has switched to user %s.',$user->username, $targetUser->username), [
            'contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS),
        ]);
    }
}
