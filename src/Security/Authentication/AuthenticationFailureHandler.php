<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

/**
 * Class with the custom Contao authentication failure handling logic.
 *
 * @author David Greminger <https://github.com/bytehead>
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * Stores the security exception in the session,.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        /** @var SessionInterface $session */
        $session = $request->getSession();
        $session->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($request->headers->get('referer'));
    }
}
