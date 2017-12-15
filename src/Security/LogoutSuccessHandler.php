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
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @param HttpUtils $httpUtils
     *
     * @internal param RouterInterface $router
     */
    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    /**
     * Redirects the user upon logout.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        if (null !== $session && $session->has('_contao_logout_target')) {
            return $this->httpUtils->createRedirectResponse($request, $session->get('_contao_logout_target'));
        }

        return $this->httpUtils->createRedirectResponse($request, 'contao_root');
    }
}
