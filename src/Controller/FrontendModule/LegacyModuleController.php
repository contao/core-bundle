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
use Contao\CoreBundle\Exception\ResponseException;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Response;

class LegacyModuleController extends FrontendModuleController
{
    /**
     * Render a legacy module as a controller.
     *
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     *
     * @return Response
     */
    public function indexAction(ModuleModel $moduleModel, $inColumn)
    {
        try {
            $this->framework->initialize();

            $legacy = $this->framework->getAdapter('Contao\Controller')
                        ->getFrontendModule($moduleModel->id, $inColumn, true);

            // Make sure reponse is never cached for legacy modules
            $response = new Response($legacy);
            $response->setPrivate();

            return $response;

        } catch (ResponseException $e) {

            return $e->getResponse();
        }
    }
}
