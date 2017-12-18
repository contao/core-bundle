<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Logout;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Http\ParameterBagUtils;

class FrontendLogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request): RedirectResponse
    {
        if ($targetUrl = ParameterBagUtils::getRequestParameterValue($request, 'redirect')) {
            return $this->httpUtils->createRedirectResponse($request, $targetUrl);
        }

        if ($targetUrl = $request->headers->get('Referer')) {
            return $this->httpUtils->createRedirectResponse($request, $targetUrl);
        }

        return parent::onLogoutSuccess($request);
    }
}
