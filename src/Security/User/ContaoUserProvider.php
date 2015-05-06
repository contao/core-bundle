<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\User;

use Contao\BackendUser;
use Terminal42\ContaoAdapterBundle\Adapter\BackendUserAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\FrontendUserAdapter;
use Contao\FrontendUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * Provides a Contao front end or back end user object.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ContaoUserProvider implements UserProviderInterface
{
    /**
     * Front end user
     * @var FrontendUserAdapter
     */
    private $frontendUser;

    /**
     * Back end user
     * @var BackendUserAdapter
     */
    private $backendUser;


    /**
     * Constructor.
     *
     * @param FrontendUserAdapter $frontendUser
     * @param BackendUserAdapter  $backendUser
     */
    public function __construct(
        FrontendUserAdapter $frontendUser,
        BackendUserAdapter $backendUser
    ) {
        $this->frontendUser = $frontendUser;
        $this->backendUser  = $backendUser;
    }


    /**
     * {@inheritdoc}
     *
     * @return BackendUser|FrontendUser The user object
     */
    public function loadUserByUsername($username)
    {
        if ('backend' === $username) {
            return $this->backendUser->instantiate();
        }

        if ('frontend' === $username) {
            return $this->frontendUser->instantiate();
        }

        throw new UsernameNotFoundException('Can only load user "frontend" or "backend".');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException('Cannot refresh a Contao user.');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class instanceof FrontendUserAdapter
            || $class instanceof BackendUserAdapter;
    }
}
