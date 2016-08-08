<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao ESI requests. This might be subject to change
 * in the very near future which is why this class is declared final.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
final class EsiController extends Controller
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * NonCacheableInsertTagsController constructor.
     *
     * @param $framework
     */
    public function __construct($framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param string $insertTag
     */
    public function renderInsertTag($insertTag)
    {
        $this->framework->initialize();

        try {
            $result = $this->framework->createInstance('Contao\InsertTags')
                ->replace($insertTag, false);
            $response = new Response($result);

        } catch (ResponseException $e) {
            $response = $e->getResponse();
        }

        $response->setPrivate();

        return $response;
    }

    /**
     * @param int    $feModuleId
     * @param string $inColumn
     * @param null   $pageId
     * @param bool   $loadPageInfo
     * @param array  $varyHeaders
     * @param int    $sharedMaxAge
     *
     * @return Response
     */
    public function renderFrontendModule(
        $feModuleId,
        $inColumn,
        $pageId = null,
        $loadPageInfo = false,
        array $varyHeaders = [],
        $sharedMaxAge = 0
    ) {
        // Make sure params have the correct type (e.g. SF ESI renders a "true"
        // as 1 for a request attribute)
        $feModuleId     = (int) $feModuleId;
        $inColumn       = (string) $inColumn;
        $pageId         = (int) $pageId;
        $pageId         = 0 === $pageId ? null : $pageId;
        $loadPageInfo   = (bool) $loadPageInfo;
        $varyHeaders    = (array) $varyHeaders;
        $sharedMaxAge   = (int) $sharedMaxAge;

        $this->framework->initialize();

        if (true === $loadPageInfo && null !== $pageId) {
            $page = $this->framework->getAdapter('Contao\PageModel')
                        ->findWithDetails($pageId);

            if (null !== $page) {
                $GLOBALS['objPage'] = $page;
            }
        }

        try {
            $result = $this->framework->getAdapter('Contao\Controller')
                ->getFrontendModule($feModuleId, $inColumn, true);
            $response = new Response($result);

        } catch (ResponseException $e) {
            $response = $e->getResponse();
        }

        if ($sharedMaxAge > 0) {
            $response->setSharedMaxAge($sharedMaxAge);
        }

        if (0 !== count($varyHeaders)) {
            $response->setVary($varyHeaders);
        }

        return $response;
    }
}
