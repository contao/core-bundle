<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace App\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\AbstractFragmentController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractContentElementController extends AbstractFragmentController
{
    /**
     * @param Request      $request
     * @param ContentModel $module
     * @param string       $section
     *
     * @return Response
     */
    public function __invoke(Request $request, ContentModel $module, string $section)
    {
        $template = $this->createTemplate($module, 'ce_');

        $template->inColumn = $section;

        if (is_array($classes = $request->attributes->get('classes'))) {
            $template->class .= ' ' . implode(' ', $classes);
        }

        return $this->getResponse($template, $module, $request);
    }
}
