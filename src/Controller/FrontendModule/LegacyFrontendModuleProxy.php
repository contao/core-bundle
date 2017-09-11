<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy for new front end module fragments so they are accessible
 * via $GLOBALS['FE_MOD'].
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class LegacyFrontendModuleProxy
{
    /**
     * @var ModuleModel
     */
    private $moduleModel;

    /**
     * @var string
     */
    private $inColumn;

    /**
     * LegacyFrontendModuleProxy constructor.
     *
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     */
    public function __construct(ModuleModel $moduleModel, $inColumn)
    {
        $this->moduleModel = $moduleModel;
        $this->inColumn    = $inColumn;
    }

    /**
     * @return string
     */
    public function generate()
    {
        @trigger_error('Using $GLOBALS[\'FE_MOD\'] has been deprecated and will no longer work in Contao 5.0. Use the fragment registry instead.', E_USER_DEPRECATED);

        $container = \System::getContainer();

        /** @var FragmentRegistryInterface $fragmentRegistry */
        $fragmentRegistry = $container->get('contao.fragment_registry');

        $fragment = $fragmentRegistry->getFragment($this->moduleModel->type);
        $response = new Response();

        if (null !== $fragment) {
            $config = new FrontendModuleConfiguration();
            $config->setModuleModel($this->moduleModel);
            $config->setInColumn($this->inColumn);

            $result = $fragmentRegistry->renderFragment($fragment, $config);

            if (null !== $result) {
                $response->setContent($result);
            }
        }

        return $response->getContent();
    }
}
