<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\FrontendTemplate;
use Contao\Model;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractFragmentController extends Controller
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options
     */
    public function setFragmentOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param Model  $model
     * @param string $prefix
     *
     * @return Template
     */
    protected function createTemplate(Model $model, string $prefix): Template
    {
        $type = $this->getType();

        if ($model->customTpl) {
            $template = new FrontendTemplate($model->customTpl);
        } else {
            $template = new FrontendTemplate($prefix.$type);
        }

        $this->initializeTemplate($template, $model, $prefix.$type);

        return $template;
    }

    /**
     * @param Template $template
     * @param Model    $model
     * @param string   $class
     */
    protected function initializeTemplate(Template $template, Model $model, string $class): void
    {
        $template->setData($model->row());

        $headline = StringUtil::deserialize($model->headline);
        $template->headline = \is_array($headline) ? $headline['value'] : $headline;
        $template->hl = \is_array($headline) ? $headline['unit'] : 'h1';

        $cssID = StringUtil::deserialize($model->cssID, true);
        $template->class = trim($class . ' ' . $cssID[1]);
        $template->cssID = !empty($cssID[0]) ? ' id="' . $cssID[0] . '"' : '';

        $template->style = $this->getStyles();
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        if (isset($this->options['type'])) {
            return $this->options['type'];
        }

        $className = ltrim(strrchr(static::class, '\\'), '\\');

        if ('Controller' === substr($className, -10)) {
            $className = substr($className, 0, -10);
        }

        return Container::underscore($className);
    }

    /**
     * @return array
     */
    protected function getStyles(): array
    {
        return [];
    }

    /**
     * @param Template|\stdClass $template
     * @param Model              $model
     * @param Request            $request
     *
     * @return Response
     */
    abstract protected function getResponse(Template $template, Model $model, Request $request): Response;
}
