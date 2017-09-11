<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
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
        @trigger_error('Using $GLOBALS[\'TL_PTY\'] has been deprecated and will no longer work in Contao 5.0. Use the fragment registry instead.', E_USER_DEPRECATED);

        global $objPage;
        $container = \System::getContainer();

        /** @var FragmentRegistryInterface $fragmentRegistry */
        $fragmentRegistry = $container->get('contao.fragment_registry');

        $fragment = $fragmentRegistry->getFragment($objPage->type);

        if (null !== $fragment) {
            // Force rendering inline (it never makes sense to render a page type as esi or anything else)
            $result = $fragmentRegistry->renderFragment($fragment, [
                'pageModel' => $objPage,
            ]);

            return new Response($result ?: '');
        }

        return new Response('');
    }
}
