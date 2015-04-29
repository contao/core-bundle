<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Adapter\BackendConfirmAdapterInterface;
use Contao\CoreBundle\Adapter\BackendFileAdapterInterface;
use Contao\CoreBundle\Adapter\BackendHelpAdapterInterface;
use Contao\CoreBundle\Adapter\BackendIndexAdapterInterface;
use Contao\CoreBundle\Adapter\BackendInstallAdapterInterface;
use Contao\CoreBundle\Adapter\BackendMainAdapterInterface;
use Contao\CoreBundle\Adapter\BackendPageAdapterInterface;
use Contao\CoreBundle\Adapter\BackendPasswordAdapterInterface;
use Contao\CoreBundle\Adapter\BackendPopupAdapterInterface;
use Contao\CoreBundle\Adapter\BackendPreviewAdapterInterface;
use Contao\CoreBundle\Adapter\BackendSwitchAdapterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao backend routes.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @Route("/contao", defaults={"_scope" = "backend"}, service="contao.controller.backend")
 */
class BackendController extends Controller
{
    /**
     * Back end main
     * @var BackendMainAdapterInterface
     */
    private $backendMain;
    /**
     * Back end index
     * @var BackendIndexAdapterInterface
     */
    private $backendIndex;

    /**
     * Back end install
     * @var BackendInstallAdapterInterface
     */
    private $backendInstall;

    /**
     * Back end password
     * @var BackendPasswordAdapterInterface
     */
    private $backendPassword;

    /**
     * Back end preview
     * @var BackendPreviewAdapterInterface
     */
    private $backendPreview;

    /**
     * Back end confirm
     * @var BackendConfirmAdapterInterface
     */
    private $backendConfirm;

    /**
     * Back end file
     * @var BackendFileAdapterInterface
     */
    private $backendFile;

    /**
     * Back end help
     * @var BackendHelpAdapterInterface
     */
    private $backendHelp;

    /**
     * Back end page
     * @var BackendPageAdapterInterface
     */
    private $backendPage;

    /**
     * Back end popup
     * @var BackendPopupAdapterInterface
     */
    private $backendPopup;

    /**
     * Back end switch
     * @var BackendSwitchAdapterInterface
     */
    private $backendSwitch;

    /**
     * Constructor.
     *
     * @param BackendMainAdapterInterface     $backendMain
     * @param BackendIndexAdapterInterface    $backendIndex
     * @param BackendInstallAdapterInterface  $backendInstall
     * @param BackendPasswordAdapterInterface $backendPassword
     * @param BackendPreviewAdapterInterface  $backendPreview
     * @param BackendConfirmAdapterInterface  $backendConfirm
     * @param BackendFileAdapterInterface     $backendFile
     * @param BackendHelpAdapterInterface     $backendHelp
     * @param BackendPageAdapterInterface     $backendPage
     * @param BackendPopupAdapterInterface    $backendPopup
     * @param BackendSwitchAdapterInterface   $backendSwitch
     */
    public function __construct(
        BackendMainAdapterInterface $backendMain,
        BackendIndexAdapterInterface $backendIndex,
        BackendInstallAdapterInterface $backendInstall,
        BackendPasswordAdapterInterface $backendPassword,
        BackendPreviewAdapterInterface $backendPreview,
        BackendConfirmAdapterInterface $backendConfirm,
        BackendFileAdapterInterface $backendFile,
        BackendHelpAdapterInterface $backendHelp,
        BackendPageAdapterInterface $backendPage,
        BackendPopupAdapterInterface $backendPopup,
        BackendSwitchAdapterInterface $backendSwitch
    ) {
        $this->backendMain      = $backendMain;
        $this->backendIndex     = $backendIndex;
        $this->backendInstall   = $backendInstall;
        $this->backendPassword  = $backendPassword;
        $this->backendPassword  = $backendPassword;
        $this->backendPreview   = $backendPreview;
        $this->backendConfirm   = $backendConfirm;
        $this->backendFile      = $backendFile;
        $this->backendHelp      = $backendHelp;
        $this->backendPage      = $backendPage;
        $this->backendPopup     = $backendPopup;
        $this->backendSwitch    = $backendSwitch;
    }

    /**
     * Runs the main back end controller.
     *
     * @return Response
     *
     * @Route("", name="contao_backend")
     */
    public function mainAction()
    {
        return $this->backendMain->run();
    }

    /**
     * Renders the back end login form.
     *
     * @return Response
     *
     * @Route("/login", name="contao_backend_login")
     */
    public function loginAction()
    {
        return $this->backendIndex->run();
    }

    /**
     * Renders the install tool.
     *
     * @return Response
     *
     * @todo Make the install tool stand-alone
     *
     * @Route("/install", name="contao_backend_install")
     */
    public function installAction()
    {
        ob_start();

        $this->backendInstall->run();

        return new Response(ob_get_clean());
    }

    /**
     * Renders the "set new password" form.
     *
     * @return Response
     *
     * @Route("/password", name="contao_backend_password")
     */
    public function passwordAction()
    {
        return $this->backendPassword->run();
    }

    /**
     * Renders the front end preview.
     *
     * @return Response
     *
     * @Route("/preview", name="contao_backend_preview")
     */
    public function previewAction()
    {
        return $this->backendPreview->run();
    }

    /**
     * Renders the "invalid request token" screen.
     *
     * @return Response
     *
     * @Route("/confirm", name="contao_backend_confirm")
     */
    public function confirmAction()
    {
        return $this->backendConfirm->run();
    }

    /**
     * Renders the file picker.
     *
     * @return Response
     *
     * @Route("/file", name="contao_backend_file")
     */
    public function fileAction()
    {
        return $this->backendFile->run();
    }

    /**
     * Renders the help content.
     *
     * @return Response
     *
     * @Route("/help", name="contao_backend_help")
     */
    public function helpAction()
    {
        return $this->backendHelp->run();
    }

    /**
     * Renders the page picker.
     *
     * @return Response
     *
     * @Route("/page", name="contao_backend_page")
     */
    public function pageAction()
    {
        return $this->backendPage->run();
    }

    /**
     * Renders the pop-up content.
     *
     * @return Response
     *
     * @Route("/popup", name="contao_backend_popup")
     */
    public function popupAction()
    {
        return $this->backendPopup->run();
    }

    /**
     * Renders the front end preview switcher.
     *
     * @return Response
     *
     * @Route("/switch", name="contao_backend_switch")
     */
    public function switchAction()
    {
        return $this->backendSwitch->run();
    }
}
