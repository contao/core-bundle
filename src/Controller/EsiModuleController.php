<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao ESI Module Controller. This might be subject to change
 * in the very near future which is why this class is declared final.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
final class EsiModuleController extends Controller
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
     * @param int    $pageId
     * @param bool   $ignorePageInfo
     * @param int    $feModuleId
     * @param string $inColumn
     * @param array  $varyHeaders
     * @param int    $sharedMaxAge
     *
     * @return Response
     */
    public function renderAction(
        $pageId,
        $ignorePageInfo,
        $feModuleId,
        $inColumn,
        array $varyHeaders,
        $sharedMaxAge
    ) {
        $this->framework->initialize();

        if (false === $ignorePageInfo) {
            $page = $this->framework->getAdapter('Contao\PageModel')
                        ->findWithDetails($pageId);

            if (null !== $page) {
                $GLOBALS['objPage'] = $page;
            }
        }

        $result = $this->framework->getAdapter('Contao\Controller')
                        ->getFrontendModule($feModuleId, $inColumn);

        $response = new Response($result);

        if ($sharedMaxAge > 0) {
            $response->setSharedMaxAge($sharedMaxAge);
        }

        if (0 !== count($varyHeaders)) {
            $response->setVary($varyHeaders);
        }

        return $response;
    }
}
