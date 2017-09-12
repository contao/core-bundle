<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\CoreBundle\Controller\FrontendModule\PageTypeRendererInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy for new page type fragments so they are accessible
 * via $GLOBALS['TL_PTY'].
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class LegacyPageTypeProxy
{
    /**
     * @return Response
     */
    public function getResponse()
    {
        global $objPage;
        $container = \System::getContainer();
        $response = new Response();

        /** @var PageTypeRendererInterface $pageTypeRenderer */
        $pageTypeRenderer = $container->get('contao.fragment.renderer.page_type');

        $result = $pageTypeRenderer->render($objPage);

        if (null !== $result) {
            $response->setContent($result);
        }

        return $response;
    }
}
