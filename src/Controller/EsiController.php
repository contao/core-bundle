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
     * EsiController constructor.
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
    public function renderNonCacheableInsertTag($insertTag)
    {
        $this->framework->initialize();

        $result = $this->framework->createInstance('Contao\InsertTags')
            ->replace($insertTag, false);

        $response = new Response($result);

        // Never cache non cacheable insert tags
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

            // Check if the page type supports adding more information to
            // the page object
            if (null !== $page
                && isset($GLOBALS['TL_PTY'][$page->type])
                && class_exists($GLOBALS['TL_PTY'][$page->type])) {
                $pageType = new $GLOBALS['TL_PTY'][$page->type]();

                // We're not using interfaces here because we want to eventually
                // turn page types into proper services too. This is when we'll
                // implement interfaces.
                if (method_exists($pageType, 'preparePage')) {
                    $pageType->preparePage($page);
                }

                $GLOBALS['objPage'] = $page;
            }
        }

        $result = $this->framework->getAdapter('Contao\Controller')
                ->getFrontendModule((int) $feModuleId, (string) $inColumn, true);

        $response = new Response($result);

        // Never cache on front end preview
        if (true === BE_USER_LOGGED_IN) {
            $response->setPrivate();
            return $response;
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
