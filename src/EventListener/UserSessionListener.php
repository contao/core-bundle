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

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\User;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[AsEventListener]
class UserSessionListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Replaces the current session data with the stored session data.
     */
    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isContaoMainRequest($event)) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $session = $user->session;

        if (\is_array($session)) {
            $sessionBag = $this->getSessionBag($event->getRequest());
            $sessionBag->replace($session);
        }

        // Dynamically register the kernel.response listener (see #1293)
        $this->eventDispatcher->addListener(KernelEvents::RESPONSE, $this->write(...));
    }

    /**
     * Writes the current session data to the database.
     */
    public function write(ResponseEvent $event): void
    {
        if (!$this->scopeMatcher->isContaoMainRequest($event)) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return;
        }

        $sessionBag = $this->getSessionBag($event->getRequest());
        $data = $sessionBag->all();

        $this->connection->update($user->getTable(), ['session' => serialize($data)], ['id' => $user->id]);
    }

    /**
     * Returns the session bag.
     */
    private function getSessionBag(Request $request): AttributeBagInterface
    {
        if (!$request->hasSession()) {
            throw new \RuntimeException('The request did not contain a session.');
        }

        $name = 'contao_frontend';

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $name = 'contao_backend';
        }

        $bag = $request->getSession()->getBag($name);

        if ($bag instanceof AttributeBagInterface) {
            return $bag;
        }

        throw new \RuntimeException(sprintf('Expected an attribute bag, got %s.', get_debug_type($bag)));
    }
}
