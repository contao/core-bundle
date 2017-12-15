<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    /**
     * @param HttpUtils            $httpUtils
     * @param ScopeMatcher         $scopeMatcher
     */
    public function __construct(HttpUtils $httpUtils, ScopeMatcher $scopeMatcher)
    {
        $this->httpUtils = $httpUtils;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Stores the security exception in the session.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @throws \RuntimeException
     *
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        /** @var Session $session */
        $session = $request->getSession();

        if (null !== $session) {
            $session->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Determines the redirect target based on the request.
     *
     * @param Request $request
     *
     * @return string
     */
    private function determineTargetUrl(Request $request): string
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            return 'contao_backend_login';
        }

        return (string) $request->headers->get('referer', '/');
    }
}
