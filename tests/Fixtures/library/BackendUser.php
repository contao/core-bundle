<?php

namespace Contao\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class BackendUser extends \Contao\User implements UserInterface
{
    const SECURITY_SESSION_KEY = '_security_contao_backend';

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
