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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param HttpUtils            $httpUtils
     * @param ScopeMatcher         $scopeMatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(HttpUtils $httpUtils, ScopeMatcher $scopeMatcher, LoggerInterface $logger = null)
    {
        $this->httpUtils = $httpUtils;
        $this->scopeMatcher = $scopeMatcher;
        $this->logger = $logger;
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
        $user = $exception instanceof AccountStatusException ? $exception->getUser() : null;
        $username = $user instanceof UserInterface ? $user->getUsername() : '';

        $this->logger->info(
            $exception->getMessage(),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, $username)]
        );

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
