<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\BackendUser;
use Contao\FrontendUser;
use Contao\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Provides a Contao authentication token.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @internal
 */
class ContaoToken extends AbstractToken
{
    /**
     * Constructor.
     *
     * @param User $user The user object
     */
    public function __construct(User $user)
    {
        if (!$user->authenticate()) {
            throw new UsernameNotFoundException('Invalid Contao user given.');
        }

        $this->setUser($user);
        $this->setAuthenticated(true);

        parent::__construct($this->getRolesFromUser($user));
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * Returns the roles depending on the user object.
     *
     * @param User $user The user object
     *
     * @return RoleInterface[] The roles
     */
    private function getRolesFromUser(User $user)
    {
        $roles = [];

        if ($user instanceof FrontendUser) {
            $roles[] = 'ROLE_MEMBER';
        } elseif ($user instanceof BackendUser) {
            $roles[] = 'ROLE_USER';

            if ($user->isAdmin) {
                $roles[] = 'ROLE_ADMIN';
            }
        }

        return $roles;
    }
}
