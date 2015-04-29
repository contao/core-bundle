<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\CoreBundle\Adapter\BackendUserAdapterInterface;
use Contao\CoreBundle\Adapter\FrontendUserAdapterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Provides a Contao authentication token.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoToken extends AbstractToken
{
    /**
     * Constructor.
     *
     * @param $user The user object
     */
    public function __construct($user)
    {
        if (!$user instanceof BackendUserAdapterInterface
        && !$user instanceof FrontendUserAdapterInterface
        ) {
            throw new UsernameNotFoundException('Invalid Contao user given.');
        }

        $this->setUser($user);

        if (!$user->authenticate()) {
            throw new UsernameNotFoundException('Invalid Contao user given.');
        }

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
     * @param $user The user object
     *
     * @return RoleInterface[] The roles
     */
    private function getRolesFromUser($user)
    {
        $roles = [];

        if ($user instanceof FrontendUserAdapterInterface) {
            $roles[] = 'ROLE_MEMBER';
        } elseif ($user instanceof BackendUserAdapterInterface) {
            $roles[] = 'ROLE_USER';

            if ($user->getValue('isAdmin')) {
                $roles[] = 'ROLE_ADMIN';
            }
        }

        return $roles;
    }
}
