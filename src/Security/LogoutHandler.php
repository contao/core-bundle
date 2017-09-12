<?php

declare(strict_types=1);

namespace Contao\CoreBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        dump($request);
        dump($response);
        dump($token);
        die();
    }
}
