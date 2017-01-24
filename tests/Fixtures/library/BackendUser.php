<?php

namespace Contao\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class BackendUser extends \Contao\User implements UserInterface
{
    public $isAdmin = true;

    public static function getInstance()
    {
        return new self();
    }

    public function authenticate()
    {
        return true;
    }

    public function setUserFromDb()
    {
        // ignore
    }

    public static function loadUserByUsername()
    {

    }

    public function getRoles()
    {

    }

    public function getPassword()
    {

    }


    public function getSalt()
    {

    }

    public function getUsername()
    {

    }

    public function eraseCredentials()
    {

    }
}
