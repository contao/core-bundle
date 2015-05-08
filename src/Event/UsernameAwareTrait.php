<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

/**
 * Adds a $username property with getters and setters.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait UsernameAwareTrait
{
    /**
     * @var string
     */
    private $username;

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
}
