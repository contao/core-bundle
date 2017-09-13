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
use Contao\FrontendUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides a Contao front end user object.
 */
class ContaoFrontendUserProvider implements UserProviderInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username): UserInterface
    {
        $this->framework->initialize();

        /** @var FrontendUser $adapter */
        $adapter = $this->framework->getAdapter(FrontendUser::class);

        if (($user = $adapter->loadUserByUsername($username)) instanceof FrontendUser) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param UserInterface $user
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof FrontendUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return FrontendUser::class === $class;
    }
}
