<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class with the custom Contao logout success handling logic.
 *
 * @author David Greminger <https://github.com/bytehead>
 */
class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Creates a custom redirecting Response object to send upon a successful logout.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        if ($session->has('_contao_logout_target')) {
            return new RedirectResponse($session->get('_contao_logout_target'));
        }

        return new RedirectResponse($this->router->generate('contao_root'));
    }
}
