<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\Logout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request): Response
    {
        if ($targetUrl = $request->query->get('redirect')) {
            return $this->httpUtils->createRedirectResponse($request, $targetUrl);
        }

        if ($targetUrl = $request->headers->get('Referer')) {
            return $this->httpUtils->createRedirectResponse($request, $targetUrl);
        }

        return parent::onLogoutSuccess($request);
    }
}
