<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic to import a user.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ImportUserEvent extends Event
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
     * @var string
     */
    private $table;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * Constructor.
     *
     * @param string $username The username
     * @param string $password The password
     * @param string $table    The table name
     */
    public function __construct(&$username, &$password, &$table)
    {
        $this->username = &$username;
        $this->password = &$password;
        $this->table    = &$table;
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
     * Sets the username.
     *
     * @param string $username The username
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
     * Sets the password.
     *
     * @param string $password The password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the table name.
     *
     * @return string The table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the table name.
     *
     * @param string $table The table name
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Returns the loading status.
     *
     * @return bool The loading status
     */
    public function getLoaded()
    {
        return $this->loaded;
    }

    /**
     * Sets the loading status.
     *
     * @param bool $loaded The loading status
     */
    public function setLoaded($loaded)
    {
        $this->loaded = (bool) $loaded;
    }
}
