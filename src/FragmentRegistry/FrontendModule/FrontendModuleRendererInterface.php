<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\FrontendModule;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ModuleModel;

/**
 * Interface FrontendModuleRendererInterface.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FrontendModuleRendererInterface
{
    /**
     * Checks if the renderer is supported.
     *
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return bool
     */
    public function supports(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool;

    /**
     * Renders the fragment.
     *
     * @param ModuleModel $moduleModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return null|string
     */
    public function render(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string;
}
