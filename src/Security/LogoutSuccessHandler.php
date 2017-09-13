<?php

declare(strict_types=1);

namespace Contao\CoreBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        if ($session->has('_contao_logout_target')) {
            return new RedirectResponse($session->get('_contao_logout_target'));
        }

        return new RedirectResponse($this->router->generate('contao_root'));
    }

}
