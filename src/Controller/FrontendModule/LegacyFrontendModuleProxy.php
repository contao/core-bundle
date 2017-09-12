<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\Module;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy for new front end module fragments so they are accessible
 * via $GLOBALS['FE_MOD'].
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class LegacyFrontendModuleProxy extends Module
{
    /**
     * @return string
     */
    public function generate()
    {
        @trigger_error('Using $GLOBALS[\'FE_MOD\'] has been deprecated and will no longer work in Contao 5.0. Use the fragment registry instead.', E_USER_DEPRECATED);

        $container = \System::getContainer();
        $response = new Response();

        /** @var FrontendModuleRendererInterface $frontendModuleRenderer */
        $frontendModuleRenderer = $container->get('contao.fragment.renderer.frontend');

        $result = $frontendModuleRenderer->render(
            $this->objModel->type,
            $this->objModel,
            $this->strColumn
        );

        if (null !== $result) {
            $response->setContent($result);
        }

        return $response->getContent();
    }

    /**
     * Compile the current element
     */
    protected function compile()
    {
        // noop
    }
}
