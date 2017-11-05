<?php

namespace Contao\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class FrontendUser extends \Contao\User implements UserInterface
{
    public $authenticated = true;

    public static function getInstance()
    {
        return new self();
    }

    public function authenticate()
    {
        return $this->authenticated;
    }

    public function setUserFromDb()
    {
        // ignore
    }

    public static function loadUserByUsername($username)
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
