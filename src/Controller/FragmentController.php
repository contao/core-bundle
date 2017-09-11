<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles Contao fragments.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentController
{
    /**
     * @var FragmentRegistryInterface
     */
    private $fragmentRegistry;

    /**
     * FragmentController constructor.
     *
     * @param FragmentRegistryInterface $fragmentRegistry
     */
    public function __construct(FragmentRegistryInterface $fragmentRegistry)
    {
        $this->fragmentRegistry = $fragmentRegistry;
    }

    /**
     * Renders any Contao fragment.
     *
     * @return Response
     *
     * @Route("/_contao/fragment", name="contao_fragment")
     */
    public function renderAction(Request $request)
    {
        $fragment = $this->fragmentRegistry->getFragment($request->attributes->get('_fragment_identifier'));

        if (null === $fragment) {
            throw new BadRequestHttpException('This fragment could not be rendered.');
        }

        return $fragment->renderAction($fragment->convertRequestToConfiguration($request));
    }
}
