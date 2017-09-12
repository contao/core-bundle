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
use Contao\CoreBundle\Controller\FragmentRegistry\AbstractFragmentRenderer;
use Contao\ModuleModel;

/**
 * Class GeneralFrontendModuleRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DefaultFrontendModuleRenderer extends AbstractFragmentRenderer implements FrontendModuleRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string
    {
        $query = [];
        $attributes = [
            'moduleModel' => $moduleModel->id,
            'inColumn' => $inColumn,
            'scope' => $scope,
        ];

        $fragmentIdentifier = 'contao.frontend_module.' . $moduleModel->type;

        return $this->renderDefault($fragmentIdentifier, $attributes, $query);
    }
}
