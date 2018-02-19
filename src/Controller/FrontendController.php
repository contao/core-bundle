<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Exception\InsufficientAuthenticationException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\FrontendCron;
use Contao\FrontendIndex;
use Contao\FrontendShare;
use Contao\PageError401;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\LogoutException;

/**
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction(): Response
    {
        $this->get('contao.framework')->initialize();

        $controller = new FrontendIndex();

        return $controller->run();
    }

    /**
     * @return Response
     *
     * @Route("/_contao/cron", name="contao_frontend_cron")
     */
    public function cronAction(): Response
    {
        $this->get('contao.framework')->initialize();

        $controller = new FrontendCron();

        return $controller->run();
    }

    /**
     * @return RedirectResponse
     *
     * @Route("/_contao/share", name="contao_frontend_share")
     */
    public function shareAction(): RedirectResponse
    {
        $this->get('contao.framework')->initialize();

        $controller = new FrontendShare();

        return $controller->run();
    }

    /**
     * Symfony will authenticate the user automatically by calling this route.
     *
     * @return RedirectResponse|Response
     *
     * @Route("/_contao/login", name="contao_frontend_login")
     */
    public function loginAction(): Response
    {
        $this->get('contao.framework')->initialize();

        if (!isset($GLOBALS['TL_PTY']['error_401']) || !class_exists($GLOBALS['TL_PTY']['error_401'])) {
            throw new UnauthorizedHttpException('Not authorized');
        }

        $tokenChecker = $this->get('contao.security.token_checker');

        \define('FE_USER_LOGGED_IN', $tokenChecker->hasFrontendUser());
        \define('BE_USER_LOGGED_IN', $tokenChecker->hasBackendUser() && $tokenChecker->isPreviewMode());

        /** @var PageError401 $pageHandler */
        $pageHandler = new $GLOBALS['TL_PTY']['error_401']();

        try {
            return $pageHandler->getResponse();
        } catch (ResponseException $e) {
            return $e->getResponse();
        } catch (InsufficientAuthenticationException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }
    }

    /**
     * Symfony will un-authenticate the user automatically by calling this route.
     *
     * @throws LogoutException
     *
     * @Route("/_contao/logout", name="contao_frontend_logout")
     */
    public function logoutAction(): void
    {
        throw new LogoutException('The user was not logged out correctly.');
    }
}
