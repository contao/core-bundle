<?php

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

class SwitchUserListener
{
    protected $logger;
    protected $tokenStorage;

    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    public function onSwitchUser(SwitchUserEvent $event)
    {
        /** @var BackendUser $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var BackendUser $targetUser */
        $targetUser = $event->getTargetUser();

        $this->logger->info(sprintf("User %s has switched to user %s.", $user->username, $targetUser->username));
    }
}
