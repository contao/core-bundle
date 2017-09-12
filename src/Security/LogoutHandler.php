<?php

namespace Contao\CoreBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        dump($request);
        dump($response);
        dump($token);
        die();
    }

    public function onLogoutSuccess(Request $request)
    {
        dump($request);
        die();
    }
}
