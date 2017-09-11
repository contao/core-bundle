<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\ConfigurationInterface;
use Contao\ModuleModel;

/**
 * Class FrontendModuleConfiguration
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FrontendModuleConfiguration implements ConfigurationInterface
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
     * @return ModuleModel
     */
    public function getModuleModel()
    {
        return $this->moduleModel;
    }

    /**
     * @param ModuleModel $moduleModel
     *
     * @return FrontendModuleConfiguration
     */
    public function setModuleModel(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;

        return $this;
    }

    /**
     * @return string
     */
    public function getInColumn()
    {
        return $this->inColumn;
    }

    /**
     * @param string $inColumn
     *
     * @return FrontendModuleConfiguration
     */
    public function setInColumn($inColumn)
    {
        $this->inColumn = $inColumn;

        return $this;
    }
}
