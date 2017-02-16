<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry\FragmentType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Interface for Contao content elements.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ContentElementInterface extends FragmentInterface
{
    const TAG_NAME = 'contao.content_element';

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function renderBackendAction(Request $request);
}
