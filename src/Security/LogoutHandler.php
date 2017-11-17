<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security;

use Contao\CoreBundle\Event\PostLogoutEvent;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Class with the custom Contao logout handling logic.
 */
class LogoutHandler implements LogoutHandlerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor.
     *
     * @param LoggerInterface          $logger
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->logger->info(
            sprintf('User %s has logged out.', $user->getUsername()),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
        );

        $this->eventDispatcher->dispatch(PostLogoutEvent::NAME, new PostLogoutEvent($user));
        $this->triggerLegacyPostLogoutHook($user);
    }

    /**
     * The postLogout hook is triggered after a user has logged out from the back end or front end.
     * It passes the user object as argument and does not expect a return value.
     *
     * @param User $user
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     *             Use the contao.postLogout event instead.
     */
    protected function triggerLegacyPostLogoutHook(User $user): void
    {
        @trigger_error('Using the postLogout hook has been deprecated and will no longer work in Contao 5.0. Use the contao.postLogout event instead.', E_USER_DEPRECATED);

        if (isset($GLOBALS['TL_HOOKS']['postLogout']) && is_array($GLOBALS['TL_HOOKS']['postLogout'])) {
            foreach ($GLOBALS['TL_HOOKS']['postLogout'] as $callback) {
                $user->objLogout = System::importStatic($callback[0], 'objLogout', true);
                $user->objLogout->{$callback[1]}($user);
            }
        }
    }
}
