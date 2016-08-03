<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModuleController;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HtmlModuleController
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class HtmlModuleController extends FrontendModuleController
{
    /**
     * @param Request      $request
     * @param \ModuleModel $moduleModel
     *
     * @return Response
     */
    public function indexAction(Request $request, ModuleModel $moduleModel)
    {
        // TODO: How to handle back end rendering?
        $response = new Response($moduleModel->html);

        // TODO: General caching helpers in Contao?
        $expire = new \DateTime();
        $expire->add(new \DateInterval('PT1M'));
        $response->setExpires($expire);

        $response->setPublic();

        return $response;
    }
}
