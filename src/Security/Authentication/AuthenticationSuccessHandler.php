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

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\System;
use Contao\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class with the custom Contao authentication success handling logic.
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var RouterInterface */
    protected $router;

    /**
     * Constructor.
     *
     * @param HttpUtils                $httpUtils
     * @param array                    $options
     * @param ContaoFrameworkInterface $framework
     * @param RouterInterface          $router
     */
    public function __construct(HttpUtils $httpUtils, array $options, ContaoFrameworkInterface $framework, RouterInterface $router)
    {
        $options['always_use_default_target_path'] = false;
        $options['target_path_parameter'] = '_target_path';

        parent::__construct($httpUtils, $options);

        $this->framework = $framework;
        $this->router = $router;
    }

    /**
     * This is called when an interactive authentication attempt succeeds.
     * Manages the correct redirecting of the logged in user.
     *
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        if ($request->request->has('_target_referer')) {
            return new RedirectResponse($request->request->get('_target_referer'));
        }

        $user = $token->getUser();

        if ($user instanceof FrontendUser) {
            return $this->handleFrontendUser($request, $user);
        }

        if ($user instanceof BackendUser) {
            return $this->handleBackendUser($request, $user);
        }

        return new RedirectResponse($this->determineTargetUrl($request));
    }

    /**
     * Specific logic for successful authenticated FrontendUser
     *
     * @param Request $request
     * @param FrontendUser $user
     *
     * @return RedirectResponse
     */
    protected function handleFrontendUser(Request $request, FrontendUser $user): RedirectResponse
    {
        $this->triggerLegacyPostAuthenticateHook($user);

        $groups = unserialize((string) $user->groups, false);

        if (is_array($groups)) {
            /** @var PageModel $pageModelAdapter */
            $pageModelAdapter = $this->framework->getAdapter(PageModel::class);

            $groupPage = $pageModelAdapter->findFirstActiveByMemberGroups($groups);

            if ($groupPage instanceof PageModel) {
                return new RedirectResponse($groupPage->getAbsoluteUrl());
            }
        }

        return new RedirectResponse($this->determineTargetUrl($request));
    }

    /**
     * Specific logic for successful authenticated BackendUser
     *
     * @param Request $request
     * @param BackendUser $user
     *
     * @return RedirectResponse
     */
    protected function handleBackendUser(Request $request, BackendUser $user): RedirectResponse
    {
        $this->triggerLegacyPostAuthenticateHook($user);

        $route = $request->attributes->get('_route');

        if ('contao_backend_login' !== $route) {
            $parameters = [];
            $routes = [
                'contao_backend',
                'contao_backend_preview',
            ];

            // Redirect to the last page visited upon login
            if ($request->query->count() > 0 && in_array($route, $routes, true)) {
                $parameters['referer'] = base64_encode($request->getRequestUri());
            }

            return new RedirectResponse(
                $this->router->generate(
                    'contao_backend_login', $parameters,
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        return new RedirectResponse($this->determineTargetUrl($request));
    }

    /**
     * The postAuthenticate hook is triggered after a user was authenticated.
     * It passes the user object as argument and does not expect a return value.
     *
     * @param User $user
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    protected function triggerLegacyPostAuthenticateHook(User $user): void
    {
        @trigger_error('Using the postAuthenticate hook has been deprecated and will no longer work in Contao 5.0. Extend the security.authentication_success_handler service instead.', E_USER_DEPRECATED);

        // HOOK: post authenticate callback
        if (isset($GLOBALS['TL_HOOKS']['postAuthenticate']) && is_array($GLOBALS['TL_HOOKS']['postAuthenticate'])) {
            foreach ($GLOBALS['TL_HOOKS']['postAuthenticate'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($user);
            }
        }
    }
}
