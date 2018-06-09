<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\BackendTemplate;
use Contao\CoreBundle\Controller\AbstractFragmentController;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFrontendModuleController extends AbstractFragmentController
{
    /**
     * @param Request     $request
     * @param ModuleModel $model
     * @param string      $section
     *
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null)
    {
        if ($this->showBackendWildcard($request)) {
            return $this->getBackendWildcard($model, $request);
        }

        $type = $this->getType();
        $template = $this->createTemplate($model, 'mod_'.$type);

        $this->addHeadlineToTemplate($template, $model->headline);
        $this->addCssAttributesToTemplate($template, 'mod_'.$type, $model->cssID, $classes);
        $this->addSectionToTemplate($template, $section);

        return $this->getResponse($template, $model, $request);
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

        $template->wildcard = '### '.strtoupper($this->get('translator')->trans('FMD.'.$this->getType().'.0', [], 'contao_modules')).' ###';
        $template->id = $module->id;
        $template->link = $module->name;
        $template->href = $href;

        return $template->getResponse();
    }

    /**
     * @param Template|\stdClass $template
     * @param ModuleModel        $model
     * @param Request            $request
     *
     * @return Response
     */
    abstract protected function getResponse(Template $template, ModuleModel $model, Request $request): Response;
}
