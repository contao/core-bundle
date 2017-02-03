<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for Contao insert tags.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface InsertTagInterface extends FragmentInterface
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function renderAction(Request $request);
}
