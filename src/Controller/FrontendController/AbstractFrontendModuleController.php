<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\AbstractFragmentController;
use Contao\BackendTemplate;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFrontendModuleController extends AbstractFragmentController
{
    /**
     * @param Request     $request
     * @param ModuleModel $module
     * @param string      $section
     *
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $module, string $section)
    {
        if ($this->showBackendWildcard($request)) {
            return $this->getBackendWildcard($module, $request);
        }

        // TODO: define a permission name
//        $this->denyAccessUnlessGranted('', $module);

        $template = $this->createTemplate($module, 'mod_');

        $template->inColumn = $section;

        if (is_array($classes = $request->attributes->get('classes'))) {
            $template->class .= ' ' . implode(' ', $classes);
        }

        return $this->getResponse($template, $module, $request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function showBackendWildcard(Request $request)
    {
        return $this->get('contao.routing.scope_matcher')->isBackendRequest($request);
    }

    /**
     * @param ModuleModel $module
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getBackendWildcard(ModuleModel $module, Request $request)
    {
        $href = $this->get('router')->generate(
            'contao_backend',
            ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $module->id]
        );

        $template = new BackendTemplate('be_wildcard');

        $template->wildcard = '### ' . strtoupper($GLOBALS['TL_LANG']['FMD'][$this->getType()][0]) . ' ###';
        $template->id = $module->id;
        $template->link = $module->name;
        $template->href = $href;

        return $template->getResponse();
    }
}
