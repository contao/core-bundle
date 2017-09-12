<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ModuleModel;

/**
 * Class GeneralFrontendModuleRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FrontendModuleRendererInterface
{
    /**
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return bool
     */
    public function supports(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool;

    /**
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return null|string
     */
    public function render(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string;
}
