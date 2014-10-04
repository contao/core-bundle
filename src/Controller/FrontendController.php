<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Controller;

use Contao\FrontendIndex;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Maps the Symfony front end controller to the Contao front end controller
 *
 * @author Leo Feyer <https://contao.org>
 */
class FrontendController extends Controller
{
    /**
     * Runs the Contao front end controller
     *
     * @return Response The response object
     */
    public function indexAction()
    {
        $controller = new FrontendIndex();

        return $controller->run();
    }
}
