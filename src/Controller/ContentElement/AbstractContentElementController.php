<?php

declare(strict_types=1);

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
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractContentElementController extends AbstractFragmentController
{
    /**
     * @param Request      $request
     * @param ContentModel $model
     * @param string       $section
     *
     * @return Response
     */
    public function __invoke(Request $request, ContentModel $model, string $section)
    {
        $template = $this->createTemplate($model, 'ce_');

        $template->inColumn = $section;

        if (is_array($classes = $request->attributes->get('classes'))) {
            $template->class .= ' '.implode(' ', $classes);
        }

        return $this->getResponse($template, $model, $request);
    }

    /**
     * @param Template|\stdClass $template
     * @param ContentModel       $model
     * @param Request            $request
     *
     * @return Response
     */
    abstract protected function getResponse(Template $template, ContentModel $model, Request $request): Response;
}
