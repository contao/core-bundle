<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller as ContaoController;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows an information screen if there is a version conflict.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class VersionConflictController extends Controller
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Adds the diff view and referer URL.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/contao/conflict", name="contao_backend_conflict")
     */
    public function indexAction(Request $request)
    {
        $this->framework = $this->get('contao.framework');
        $this->framework->initialize();

        $template = $this->getTemplate();

        $template->explain1 = sprintf(
            $GLOBALS['TL_LANG']['MSC']['versionConflict1'],
            (int) $request->query->get('theirs'),
            (int) $request->query->get('mine')
        );

        $template->explain2 = sprintf(
            $GLOBALS['TL_LANG']['MSC']['versionConflict2'],
            (int) $request->query->get('theirs') + 1,
            (int) $request->query->get('theirs')
        );

        $session = $this->get('session');

        if ($session->has('versionConflictUrl')) {
            $template->href = $session->get('versionConflictUrl');
        } else {
            $template->href = $this->get('router')->generate('contao_backend');
        }

        $versions = new Versions($request->query->get('table'), (int) $request->query->get('id'));

        $template->diff = $versions->compare(true);

        return $template->getResponse();
    }

    /**
     * Returns the template object.
     *
     * @return BackendTemplate|object
     */
    private function getTemplate()
    {
        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);
        $system->loadLanguageFile('default');

        /** @var ContaoController $controller */
        $controller = $this->framework->getAdapter(ContaoController::class);
        $controller->setStaticUrls();

        /** @var BackendTemplate|object $template */
        $template = $this->framework->createInstance(BackendTemplate::class, ['be_conflict']);
        $template->language = $GLOBALS['TL_LANGUAGE'];
        $template->h1 = $GLOBALS['TL_LANG']['MSC']['versionConflict'];
        $template->back = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $template->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['versionConflict']);

        /** @var Backend $backend */
        $backend = $this->framework->getAdapter(Backend::class);

        $template->theme = $backend->getTheme();

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $template->charset = $config->get('characterSet');

        /** @var Environment $environment */
        $environment = $this->framework->getAdapter(Environment::class);

        $template->base = $environment->get('base');

        return $template;
    }
}
