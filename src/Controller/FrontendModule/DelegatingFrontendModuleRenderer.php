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
 * Class ChainFrontendModuleRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DelegatingFrontendModuleRenderer implements FrontendModuleRendererInterface
{
    /**
     * @var FrontendModuleRendererInterface[]
     */
    private $renderers;

    /**
     * ChainFrontendModuleRenderer constructor.
     *
     * @param FrontendModuleRendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = $renderers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type, ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($type, $moduleModel, $inColumn, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $type, ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($type, $moduleModel, $inColumn, $scope)) {
                return $renderer->render($type, $moduleModel, $inColumn, $scope);
            }
        }

        return null;
    }
}
