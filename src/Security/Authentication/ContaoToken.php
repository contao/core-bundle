<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\BackendUser;
use Contao\FrontendUser;
use Contao\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Role\Role;

@trigger_error('Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

/**
 * Class ContaoToken.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 *             Use the UsernamePasswordToken instead.
 */
class ContaoToken extends AbstractToken
{
    /**
     * @param User $user
     *
     * @throws UsernameNotFoundException
     *
     * @deprecated Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.
     */
    public function __construct(User $user)
    {
        @trigger_error('Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        if (true !== $user->authenticate()) {
            throw new UsernameNotFoundException('Invalid Contao user given.');
        }

        $this->setUser($user);
        $this->setAuthenticated(true);

        parent::__construct($this->getRolesFromUser($user));
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.
     */
    public function getCredentials(): string
    {
        @trigger_error('Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        return '';
    }

    /**
     * Returns the roles depending on the user object.
     *
     * @param User $user
     *
     * @return Role[]
     *
     * @deprecated Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.
     */
    private function getRolesFromUser(User $user): array
    {
        @trigger_error('Using the ContaoToken has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

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
