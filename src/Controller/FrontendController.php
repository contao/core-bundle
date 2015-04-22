<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Adapter\FrontendCronAdapterInterface;
use Contao\CoreBundle\Adapter\FrontendIndexAdapterInterface;
use Contao\CoreBundle\Adapter\FrontendShareAdapterInterface;
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
     * @var FrontendIndexAdapterInterface
     */
    private $frontendIndex;

    /**
     * Front end cron
     * @var FrontendCronAdapterInterface
     */
    private $frontendCron;

    /**
     * Front end share
     * @var FrontendShareAdapterInterface
     */
    private $frontendShare;

    /**
     * Constructor.
     *
     * @param FrontendIndexAdapterInterface $frontendIndex
     * @param FrontendCronAdapterInterface  $frontendCron
     * @param FrontendShareAdapterInterface $frontendShare
     */
    public function __construct(
        FrontendIndexAdapterInterface $frontendIndex,
        FrontendCronAdapterInterface $frontendCron,
        FrontendShareAdapterInterface $frontendShare
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
