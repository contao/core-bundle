<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Controller;

use Contao\PageError403;
use Contao\PageError404;
use Contao\PageModel;
use Contao\PageRegular;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle the page types and render the response.
 *
 * @author Leo Feyer <https://contao.org>
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class FrontendController extends Controller
{
    /**
     * Handle regular page type.
     *
     * @param Request $request The request object.
     *
     * @return Response The response object.
     */
    public function regularPageAction(Request $request)
    {
        /** @var PageModel $page */
        $page = $request->attributes->get('contentDocument');

        /** @var PageRegular $controller */
        $controller = new $GLOBALS['TL_PTY']['regular']();

        return $controller->getResponse($page);
    }

    /**
     * Handle error_403 page type.
     *
     * @param Request $request The request object.
     *
     * @return Response The response object.
     */
    public function accessDeniedAction(Request $request)
    {
        /** @var PageModel $page */
        $page = $request->attributes->get('contentDocument');

        /** @var PageError403 $controller */
        $controller = new $GLOBALS['TL_PTY']['error_403']();

        return $controller->getResponse($page);
    }

    /**
     * Handle error_404 page type.
     *
     * @param Request $request The request object.
     *
     * @return Response The response object.
     */
    public function notFoundAction(Request $request)
    {
        /** @var PageModel $page */
        $page = $request->attributes->get('contentDocument');

        /** @var PageError404 $controller */
        $controller = new $GLOBALS['TL_PTY']['error_404']();

        return $controller->getResponse($page);
    }
}
