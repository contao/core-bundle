<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\InsertTag\Parser;

use Contao\CoreBundle\InsertTag\InsertTagCollection;
use Contao\CoreBundle\InsertTagFlag\InsertTagFlagCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an interface for InsertTag parsers.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ParserInterface
{

    /**
     * Gets the collection of InsertTags to be considered.
     *
     * @return InsertTagCollection
     */
    public function getInsertTagCollection();

    /**
     * Gets the collection of InsertTagFlags to be considered.
     *
     * @return InsertTagFlagCollection
     */
    public function getInsertTagFlagCollection();

    /**
     * Applies the current InsertTags and InsertTagFlags on a given string.
     *
     * @param         $string
     * @param Request $request
     *
     * @return string
     */
    public function parse($string, Request $request);
}
