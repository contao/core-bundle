<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Traits;

/**
 * Adds a $password property with getters and setters.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait GetPasswordTrait
{
    /**
     * @var string
     */
    private $password;

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
}
