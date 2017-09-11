<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\AbstractFragment;
use Contao\CoreBundle\Controller\FragmentRegistry\ConfigurationInterface;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract base class for front end modules.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
abstract class AbstractFrontendModule extends AbstractFragment
{
    /**
     * {@inheritdoc}
     */
    public function supportsConfiguration(ConfigurationInterface $configuration)
    {
        return $configuration instanceof FrontendModuleConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParameters(ConfigurationInterface $configuration)
    {
        /** @var FrontendModuleConfiguration $configuration */
        if (!$this->supportsConfiguration($configuration)) {
            throw new \InvalidArgumentException('Configuration must be instance of FrontendModuleConfiguration');
        }

        return [
            'moduleId' => $configuration->getModuleModel()->id
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertRequestToConfiguration(Request $request)
    {
        $config = new FrontendModuleConfiguration();

        // TODO, use adapter? (= every module needs to inject the framework)
        $config->setModuleModel(ModuleModel::findByPk($request->query->getInt('moduleId')));

        return $config;
    }
}
