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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class with the custom Contao authentication failure handling logic.
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /** @var ScopeMatcher */
    protected $scopeMatcher;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param HttpKernelInterface  $httpKernel
     * @param HttpUtils            $httpUtils
     * @param array                $options
     * @param LoggerInterface|null $logger
     * @param ScopeMatcher         $scopeMatcher
     * @param TranslatorInterface  $translator
     */
    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options, LoggerInterface $logger = null, ScopeMatcher $scopeMatcher, TranslatorInterface $translator)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);

        $this->scopeMatcher = $scopeMatcher;
        $this->translator = $translator;
    }

    /**
     * Stores the security exception in the session.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->set(Security::AUTHENTICATION_ERROR, $exception);

        $session->getFlashBag()->set(
            $this->getFlashType($request),
            $this->translator->trans('ERR.invalidLogin', [], 'contao_default')
        );

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Determines the redirect target based on the request.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request): string
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            return $request->getRequestUri();
        }

        return $request->headers->get('referer');
    }

    /**
     * Gets flash type from providerKey.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getFlashType(Request $request): string
    {
        $type = '';

        if ($this->scopeMatcher->isFrontendRequest($request)) {
            $type = 'contao.FE.error';
        }

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $type = 'contao.BE.error';
        }

        return $type;
    }
}
