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
 * Legacy listener to maintain the postLogin hook.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 */
class LegacyInteractiveLoginListener
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * The postLogin hook is triggered after a user has logged in. This can be either in the back end or the front end.
     * It passes the user object as argument and does not expect a return value.
     *
     * @param InteractiveLoginEvent $event
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        @trigger_error('Using LegacyInteractiveLoginListener::onInteractiveLogin() has been deprecated and will no longer work in Contao 5.0. Use the security.interactive_login event instead.', E_USER_DEPRECATED);

        /** @var UserInterface $user */
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        // HOOK: post login callback
        if (isset($GLOBALS['TL_HOOKS']['postLogin']) && is_array($GLOBALS['TL_HOOKS']['postLogin'])) {
            foreach ($GLOBALS['TL_HOOKS']['postLogin'] as $callback) {
                $user->objLogin = System::importStatic($callback[0], 'objLogin', true);
                $user->objLogin->{$callback[1]}($user);
            }
        }

        $this->logger->info(sprintf('User %s has logged in.', $user->username), [
            'contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS),
        ]);
    }
}
