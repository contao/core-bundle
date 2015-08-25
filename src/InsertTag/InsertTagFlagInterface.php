<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\InsertTag;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for InsertTagFlags
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface InsertTagFlagInterface
{
    /**
     * Returns the name for the InsertTagFlag
     *
     * @return string
     */
    public function getName();

    /**
     * Returns true or false whether this InsertTagFlag is responsible to replace
     * a certain string.
     *
     * @param string  $insertTagFlag
     * @param Request $request
     *
     * @return bool
     */
    public function isResponsible($insertTagFlag, Request $request);

    /**
     * Applies the InsertTagFlag on a given InsertTag and returns a proper HTTP Foundation
     * response. This works just like a normal controller so you can and should
     * take full advantage of caching mechanisms of HTTP.
     *
     * @param Response $insertTagResponse
     * @param Request  $request
     *
     * @return Response
     */
    public function getResponse(Response $insertTagResponse, Request $request);
}
