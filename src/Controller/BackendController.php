<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Terminal42\ContaoAdapterBundle\Adapter\BackendConfirmAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendFileAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendHelpAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendIndexAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendInstallAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendMainAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendPageAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendPasswordAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendPopupAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendPreviewAdapter;
use Terminal42\ContaoAdapterBundle\Adapter\BackendSwitchAdapter;
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
     * @var BackendMainAdapter
     */
    private $backendMain;
    /**
     * Back end index
     * @var BackendIndexAdapter
     */
    private $backendIndex;

    /**
     * Back end install
     * @var BackendInstallAdapter
     */
    private $backendInstall;

    /**
     * Back end password
     * @var BackendPasswordAdapter
     */
    private $backendPassword;

    /**
     * Back end preview
     * @var BackendPreviewAdapter
     */
    private $backendPreview;

    /**
     * Back end confirm
     * @var BackendConfirmAdapter
     */
    private $backendConfirm;

    /**
     * Back end file
     * @var BackendFileAdapter
     */
    private $backendFile;

    /**
     * Back end help
     * @var BackendHelpAdapter
     */
    private $backendHelp;

    /**
     * Back end page
     * @var BackendPageAdapter
     */
    private $backendPage;

    /**
     * Back end popup
     * @var BackendPopupAdapter
     */
    private $backendPopup;

    /**
     * Back end switch
     * @var BackendSwitchAdapter
     */
    private $backendSwitch;

    /**
     * Constructor.
     *
     * @param BackendMainAdapter     $backendMain
     * @param BackendIndexAdapter    $backendIndex
     * @param BackendInstallAdapter  $backendInstall
     * @param BackendPasswordAdapter $backendPassword
     * @param BackendPreviewAdapter  $backendPreview
     * @param BackendConfirmAdapter  $backendConfirm
     * @param BackendFileAdapter     $backendFile
     * @param BackendHelpAdapter     $backendHelp
     * @param BackendPageAdapter     $backendPage
     * @param BackendPopupAdapter    $backendPopup
     * @param BackendSwitchAdapter   $backendSwitch
     */
    public function __construct(
        BackendMainAdapter $backendMain,
        BackendIndexAdapter $backendIndex,
        BackendInstallAdapter $backendInstall,
        BackendPasswordAdapter $backendPassword,
        BackendPreviewAdapter $backendPreview,
        BackendConfirmAdapter $backendConfirm,
        BackendFileAdapter $backendFile,
        BackendHelpAdapter $backendHelp,
        BackendPageAdapter $backendPage,
        BackendPopupAdapter $backendPopup,
        BackendSwitchAdapter $backendSwitch
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
