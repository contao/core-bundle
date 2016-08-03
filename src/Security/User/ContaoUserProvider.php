<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\User;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\FrontendUser;
use Contao\User;
use Contao\UserModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides a Contao front end or back end user object.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ContaoUserProvider implements UserProviderInterface
{
    use ScopeAwareTrait;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param ContainerInterface
     * @param ContaoFrameworkInterface
     */
    public function __construct(ContainerInterface $container, ContaoFrameworkInterface $framework)
    {
        $this->container = $container;
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     *
     * @return BackendUser|FrontendUser
     */
    public function loadUserByUsername($username)
    {
        $this->framework->initialize();

        /** @var UserModel $userModel */
        $userModel = $this->framework->getAdapter('Contao\UserModel');
        $user = $userModel->findOneBy('username', $username);

        if (null === $user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        $user->applyArrDataToProperties();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserModel) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Contao\UserModel';
    }
}
