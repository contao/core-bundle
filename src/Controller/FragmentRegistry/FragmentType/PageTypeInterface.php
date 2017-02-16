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
 * Interface for Contao page types.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface PageTypeInterface extends FragmentInterface
{
    const TAG_NAME = 'contao.page_type';

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function renderAction(Request $request);
}
