<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Templating;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\FrontendTemplate;
use Contao\TemplateLoader;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Template engine that use the "old" contao templating system.
 *
 * @author Tristan Lins <https://bit3.de>
 */
class ContaoEngine implements EngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($name, array $parameters = array())
    {
        if ('BE' == TL_MODE) {
            $template = new BackendTemplate($name);
        } else {
            $template = new FrontendTemplate($name);
        }
        $template->setData($parameters);

        return $template->parse();
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        try {
            TemplateLoader::getPath($name, 'html5'); // FIXME static format

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return $this->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        if (!$response) {
            $response = new Response();
        }

        $response->setContent($this->render($view, $parameters));

        $response->headers->set('Vary', 'User-Agent', false);
        $response->headers->set('Content-Type', 'text/html; charset=' . Config::get('characterSet')); // FIXME: content type hardcoded

        return $response;
    }
}
