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
     * @param int    $pageId
     * @param array  $varyHeaders
     * @param int    $sharedMaxAge
     * @param string $cacheKey
     *
     * @return Response
     */
    public function renderFrontendModule(
        $feModuleId,
        $inColumn,
        $pageId = 0,
        array $varyHeaders = [],
        $sharedMaxAge = 0
    ) {
        $this->framework->initialize();

        if (0 !== (int) $pageId) {
            $page = $this->framework->getAdapter('Contao\PageModel')
                        ->findWithDetails((int) $pageId);

            if (null !== $page) {
                $GLOBALS['objPage'] = $page;
            }
        }

        try {
            $result = $this->framework->getAdapter('Contao\Controller')
                ->getFrontendModule((int) $feModuleId, (string) $inColumn, true);
            $response = new Response($result);

        } catch (ResponseException $e) {
            $response = $e->getResponse();
        }

        if ((int) $sharedMaxAge > 0) {
            $response->setSharedMaxAge((int) $sharedMaxAge);
        }

        if (0 !== count((array) $varyHeaders)) {
            $response->setVary((array) $varyHeaders);
        }

        return $response;
    }
}
