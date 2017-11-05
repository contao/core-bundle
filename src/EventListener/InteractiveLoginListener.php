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
use Contao\System;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Interactive login listener to log successful login attempts.
 */
class InteractiveLoginListener
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->logger->info(
            vsprintf(
                'User %s has logged in.',
                [$user->username]
            ),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
        );

        $this->triggerLegacyPostLoginHook($user);
    }

    /**
     * The postLogin hook is triggered after a user has logged in. This can be either in the back end or the front end.
     * It passes the user object as argument and does not expect a return value.
     *
     * @param User $user
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    protected function triggerLegacyPostLoginHook(User $user): void
    {
        @trigger_error('Using InteractiveLoginListener::triggerLegacyPostLoginHook() has been deprecated and will no longer work in Contao 5.0. Use the security.interactive_login event instead.', E_USER_DEPRECATED);

        // HOOK: post login callback
        if (isset($GLOBALS['TL_HOOKS']['postLogin']) && is_array($GLOBALS['TL_HOOKS']['postLogin'])) {
            foreach ($GLOBALS['TL_HOOKS']['postLogin'] as $callback) {
                $user->objLogin = System::importStatic($callback[0], 'objLogin', true);
                $user->objLogin->{$callback[1]}($user);
            }
        }
    }
}
