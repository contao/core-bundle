<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Terminal42\ContaoAdapterBundle\Adapter\FrontendCronAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\FrontendIndexAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\FrontendShareAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao frontend routes.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @Route(defaults={"_scope" = "frontend"}, service="contao.controller.frontend")
 */
class FrontendController extends Controller
{
    /**
     * Front end index
     * @var FrontendIndexAdapter
     */
    private $frontendIndex;

    /**
     * Front end cron
     * @var FrontendCronAdapter
     */
    private $frontendCron;

    /**
     * Front end share
     * @var FrontendShareAdapter
     */
    private $frontendShare;

    /**
     * Constructor.
     *
     * @param FrontendIndexAdapter $frontendIndex
     * @param FrontendCronAdapter  $frontendCron
     * @param FrontendShareAdapter $frontendShare
     */
    public function __construct(
        FrontendIndexAdapter $frontendIndex,
        FrontendCronAdapter $frontendCron,
        FrontendShareAdapter $frontendShare
    ) {
        $this->frontendIndex = $frontendIndex;
        $this->frontendCron  = $frontendCron;
        $this->frontendShare = $frontendShare;
    }

    /**
     * Runs the main front end controller.
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->frontendIndex->run();
    }

    /**
     * Runs the command scheduler.
     *
     * @return Response
     *
     * @Route("/_contao/cron", name="contao_frontend_cron")
     */
    public function cronAction()
    {
        return $this->frontendCron->run();
    }

    /**
     * Renders the content syndication dialog.
     *
     * @return Response
     *
     * @Route("/_contao/share", name="contao_frontend_share")
     */
    public function shareAction()
    {
        return $this->frontendShare->run();
    }
}
