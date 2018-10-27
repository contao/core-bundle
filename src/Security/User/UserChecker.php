<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\User;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Security\Exception\LockedException;
use Contao\Date;
use Contao\FrontendUser;
use Contao\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->checkIfAccountIsLocked($user);
        $this->checkIfAccountIsDisabled($user);
        $this->checkIfLoginIsAllowed($user);
        $this->checkIfAccountIsActive($user);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }

    /**
     * @throws LockedException
     */
    private function checkIfAccountIsLocked(User $user): void
    {
        $lockedSeconds = $user->locked - time();

        if ($lockedSeconds <= 0) {
            return;
        }

        $ex = new LockedException(
            $lockedSeconds,
            sprintf('User "%s" has been locked for %s minutes', $user->username, ceil($lockedSeconds / 60))
        );

        $ex->setUser($user);

        throw $ex;
    }

    private function checkIfAccountIsDisabled(User $user): void
    {
        if (!$user->disable) {
            return;
        }

        $ex = new DisabledException('The account has been disabled');
        $ex->setUser($user);

        throw $ex;
    }

    /**
     * Checks wether login is allowed (front end only).
     */
    private function checkIfLoginIsAllowed(User $user): void
    {
        if (!$user instanceof FrontendUser || $user->login) {
            return;
        }

        $ex = new DisabledException(sprintf('User "%s" is not allowed to log in', $user->username));
        $ex->setUser($user);

        throw $ex;
    }

    /**
     * Checks whether the account is not active yet or not anymore.
     */
    private function checkIfAccountIsActive(User $user): void
    {
        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $start = (int) $user->start;
        $stop = (int) $user->stop;
        $time = Date::floorToMinute(time());
        $notActiveYet = $start && $start > $time;
        $notActiveAnymore = $stop && $stop <= ($time + 60);
        $logMessage = '';

        if ($notActiveYet) {
            $logMessage = sprintf(
                'The account is not active yet (activation date: %s)',
                Date::parse($config->get('dateFormat'), $start)
            );
        }

        if ($notActiveAnymore) {
            $logMessage = sprintf(
                'The account is not active anymore (deactivation date: %s)',
                Date::parse($config->get('dateFormat'), $stop)
            );
        }

        if ('' === $logMessage) {
            return;
        }

        $ex = new DisabledException($logMessage);
        $ex->setUser($user);

        throw $ex;
    }
}
