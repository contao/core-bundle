<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\User;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ContaoUserProvider implements UserProviderInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @param ContaoFrameworkInterface $framework
     * @param string                   $userClass
     */
    public function __construct(ContaoFrameworkInterface $framework, string $userClass)
    {
        $this->framework = $framework;
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username): User
    {
        $this->framework->initialize();

        /** @var User $adapter */
        $adapter = $this->framework->getAdapter($this->userClass);
        $user = $user = $adapter->loadUserByUsername($username);

        if (is_a($user, $this->userClass)) {
            return $user;
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $this->framework->initialize();

        if (is_a($user, $this->userClass)) {
            $user = $this->loadUserByUsername($user->getUsername());

            $this->triggerPostAuthenticateHook($user);

            return $user;
        }

        throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return $this->userClass === $class;
    }

    /**
     * Triggers the postAuthenticate hook.
     *
     * @param User $user
     */
    private function triggerPostAuthenticateHook(User $user): void
    {
        if (empty($GLOBALS['TL_HOOKS']['postAuthenticate']) || !\is_array($GLOBALS['TL_HOOKS']['postAuthenticate'])) {
            return;
        }

        @trigger_error('Using the postAuthenticate hook has been deprecated and will no longer work in Contao 5.0. Use the contao.post_authenticate event instead.', E_USER_DEPRECATED);

        foreach ($GLOBALS['TL_HOOKS']['postAuthenticate'] as $callback) {
            $this->framework->createInstance($callback[0])->{$callback[1]}($user);
        }
    }
}
