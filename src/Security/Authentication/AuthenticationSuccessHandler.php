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
use Contao\CoreBundle\Event\PostAuthenticateEvent;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param HttpUtils                $httpUtils
     * @param array                    $options
     * @param ContaoFrameworkInterface $framework
     * @param RouterInterface          $router
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(HttpUtils $httpUtils, array $options, ContaoFrameworkInterface $framework, RouterInterface $router, EventDispatcherInterface $eventDispatcher)
    {
        $options['always_use_default_target_path'] = false;
        $options['target_path_parameter'] = '_target_path';

        parent::__construct($httpUtils, $options);

        $this->framework = $framework;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
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
     * Specific logic for successful authenticated FrontendUser.
     *
     * @param Request      $request
     * @param FrontendUser $user
     *
     * @return RedirectResponse
     */
    protected function handleFrontendUser(Request $request, FrontendUser $user): RedirectResponse
    {
        $this->framework->initialize();
        $this->triggerLegacyPostAuthenticateHook($user);
        $this->eventDispatcher->dispatch(PostAuthenticateEvent::NAME, new PostAuthenticateEvent($user));

        $groups = unserialize((string) $user->groups, ['allowed_classes' => false]);

        if (\is_array($groups)) {
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
     * Specific logic for successful authenticated BackendUser.
     *
     * @param Request     $request
     * @param BackendUser $user
     *
     * @return RedirectResponse
     */
    protected function handleBackendUser(Request $request, BackendUser $user): RedirectResponse
    {
        $this->framework->initialize();
        $this->triggerLegacyPostAuthenticateHook($user);
        $this->eventDispatcher->dispatch(PostAuthenticateEvent::NAME, new PostAuthenticateEvent($user));

        $route = $request->attributes->get('_route');

        if ('contao_backend_login' !== $route) {
            $parameters = [];
            $routes = [
                'contao_backend',
                'contao_backend_preview',
            ];

            // Redirect to the last page visited upon login
            if ($request->query->count() > 0 && \in_array($route, $routes, true)) {
                $parameters['referer'] = base64_encode($request->getRequestUri());
            }

            return new RedirectResponse(
                $this->router->generate(
                    'contao_backend_login', $parameters,
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * The postAuthenticate hook is triggered after a user was authenticated.
     * It passes the user object as argument and does not expect a return value.
     *
     * @param User $user
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     *             Use the contao.post_authenticate event instead.
     */
    protected function triggerLegacyPostAuthenticateHook(User $user): void
    {
        @trigger_error('Using the postAuthenticate hook has been deprecated and will no longer work in Contao 5.0. Use the contao.post_authenticate event instead.', E_USER_DEPRECATED);

        if (isset($GLOBALS['TL_HOOKS']['postAuthenticate']) && \is_array($GLOBALS['TL_HOOKS']['postAuthenticate'])) {
            foreach ($GLOBALS['TL_HOOKS']['postAuthenticate'] as $callback) {
                $this->framework->createInstance($callback[0])->{$callback[1]}($user);
            }
        }
    }
}
