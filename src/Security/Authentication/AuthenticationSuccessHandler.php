<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendUser;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Class with the custom Contao authentication success handling logic.
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $framework;

    /**
     * Constructor.
     *
     * @param HttpUtils                $httpUtils
     * @param array                    $options
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(HttpUtils $httpUtils, array $options, ContaoFrameworkInterface $framework)
    {
        $options['always_use_default_target_path'] = false;
        $options['target_path_parameter'] = '_target_path';

        parent::__construct($httpUtils, $options);

        $this->framework = $framework;
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
            $groups = unserialize((string) $user->groups, false);

            if (!empty($groups) && is_array($groups)) {
                /** @var PageModel $pageModelAdapter */
                $pageModelAdapter = $this->framework->getAdapter(PageModel::class);

                $groupPage = $pageModelAdapter->findFirstActiveByMemberGroups($groups);

                if ($groupPage instanceof PageModel) {
                    return new RedirectResponse($groupPage->getAbsoluteUrl());
                }
            }
        }

        return new RedirectResponse($this->determineTargetUrl($request));
    }
}
