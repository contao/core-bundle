<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Adapter;

/**
 * Provides an adapter interface for the Contao Frontend class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FrontendAdapterInterface
{
    /**
     * Split the current request into fragments, strip the URL suffix, recreate the $_GET array and return the page ID
     *
     * @return mixed
     */
    public function getPageIdFromUrl();

    /**
     * Return the root page ID (backwards compatibility)
     *
     * @return integer
     */
    public function getRootIdFromUrl();

    /**
     * Try to find a root page based on language and URL
     *
     * @return \PageModel
     */
    public function getRootPageFromUrl();

    /**
     * Overwrite the parent method as front end URLs are handled differently
     *
     * @param string  $strRequest
     * @param boolean $blnIgnoreParams
     * @param array   $arrUnset
     *
     * @return string
     */
    public function addToUrl($strRequest, $blnIgnoreParams = false, $arrUnset = array());

    /**
     * Get the meta data from a serialized string
     *
     * @param string $strData
     * @param string $strLanguage
     *
     * @return array
     */
    public function getMetaData($strData, $strLanguage);

    /**
     * Return the cron timeout in seconds
     *
     * @return integer
     */
    public function getCronTimeout();

    /**
     * Index a page if applicable
     *
     * @param Response $objResponse
     */
    public function indexPageIfApplicable($objResponse);

    /**
     * Check whether there is a cached version of the page and return a response object
     * @return Response|null
     */
    public function getResponseFromCache();
}
