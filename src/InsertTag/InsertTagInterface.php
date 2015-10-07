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
 * Interface for InsertTags
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface InsertTagInterface
{
    /**
     * Returns the name for the InsertTag
     *
     * @return string
     */
    public function getName();

    /**
     * Returns true or false whether this InsertTag is responsible to replace
     * a certain InsertTag string.
     *
     * @param string  $insertTag
     * @param Request $request
     *
     * @return bool
     */
    public function isResponsible($insertTag, Request $request);

    /**
     * Replaces the InsertTag string and returns a proper HTTP Foundation
     * response. This works just like a normal controller so you can and should
     * take full advantage of caching mechanisms of HTTP.
     *
     * @param string  $insertTag
     * @param Request $request
     *
     * @return Response
     */
    public function getResponse($insertTag, Request $request);
}
