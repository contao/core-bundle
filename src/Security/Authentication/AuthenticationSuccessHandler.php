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
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param HttpUtils                $httpUtils
     * @param ContaoFrameworkInterface $framework
     * @param RouterInterface          $router
     * @param array                    $options
     * @param LoggerInterface|null     $logger
     */
    public function __construct(HttpUtils $httpUtils, ContaoFrameworkInterface $framework, RouterInterface $router, array $options = [], LoggerInterface $logger = null)
    {
        $options['always_use_default_target_path'] = false;
        $options['target_path_parameter'] = '_target_path';

        parent::__construct($httpUtils, $options);

        $this->framework = $framework;
        $this->router = $router;
        $this->logger = $logger;
    }

    /**
     * Redirects the authenticated user.
     *
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return new RedirectResponse($this->determineTargetUrl($request));
        }

        $this->framework->initialize();

        $user->lastLogin = $user->currentLogin;
        $user->currentLogin = time();
        $user->save();

        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('User "%s" has logged in', $token->getUsername()),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS, $token->getUsername())]
            );
        }

        $this->triggerPostLoginHook($user);

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
     * Redirects an authenticated front end user.
     *
     * @param Request      $request
     * @param FrontendUser $user
     *
     * @return RedirectResponse
     */
    private function handleFrontendUser(Request $request, FrontendUser $user): RedirectResponse
    {
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
     * Redirects an authenticated back end user.
     *
     * @param Request     $request
     * @param BackendUser $user
     *
     * @return RedirectResponse
     */
    private function handleBackendUser(Request $request, BackendUser $user): RedirectResponse
    {
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
                $this->router->generate('contao_backend_login', $parameters, UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Triggers the postLogin hook.
     *
     * @param User $user
     */
    private function triggerPostLoginHook(User $user): void
    {
        if (empty($GLOBALS['TL_HOOKS']['postLogin']) || !\is_array($GLOBALS['TL_HOOKS']['postLogin'])) {
            return;
        }

        @trigger_error('Using the "postLogin" hook has been deprecated and will no longer work in Contao 5.0. Use the security.interactive_login event instead.', E_USER_DEPRECATED);

        foreach ($GLOBALS['TL_HOOKS']['postLogin'] as $callback) {
            $this->framework->createInstance($callback[0])->{$callback[1]}($user);
        }
    }
}
