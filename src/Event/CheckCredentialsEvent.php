<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when the login credentials are checked.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class CheckCredentialsEvent extends Event
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $authenticated = false;

    /**
     * Constructor.
     *
     * @param string $username The username
     * @param string $password The password
     * @param User   $user     The user object
     */
    public function __construct($username, $password, User $user)
    {
        $this->username = $username;
        $this->password = $password;
        $this->user = $user;
    }

    /**
     * Returns the username.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the password.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the user object.
     *
     * @return User The user object
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the authentication status.
     *
     * @return bool The authentication status
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Sets the authentication status.
     *
     * @param bool $authenticated The authentication status
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = (bool) $authenticated;
    }
}
