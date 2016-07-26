<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ArgumentConverterInterface
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ArgumentConverterInterface
{
    /**
     * Convert arguments on the request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function convertArguments(Request $request);
}
