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
use Contao\CoreBundle\Controller\FragmentRegistry\FragmentRegistryInterface;
use Contao\CoreBundle\Controller\FragmentRegistry\SimpleRenderingInformationProvidingInterface;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Class GeneralFrontendModuleRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DefaultFrontendModuleRenderer implements FrontendModuleRendererInterface
{
    /**
     * @var FragmentRegistryInterface
     */
    private $fragmentRegistry;

    /**
     * @var FragmentHandler
     */
    private $fragmentHandler;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * FrontendModuleRenderer constructor.
     *
     * @param FragmentRegistryInterface $fragmentRegistry
     * @param FragmentHandler           $fragmentHandler
     * @param RequestStack              $requestStack
     */
    public function __construct(FragmentRegistryInterface $fragmentRegistry, FragmentHandler $fragmentHandler, RequestStack $requestStack)
    {
        $this->fragmentRegistry = $fragmentRegistry;
        $this->fragmentHandler  = $fragmentHandler;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type, ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $type, ModuleModel $moduleModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string
    {
        $query = [];
        $attributes = [
            'moduleModel' => $moduleModel->id,
            'inColumn' => $inColumn,
            'scope' => $scope,
        ];

        if (isset($GLOBALS['objPage'])) {
            $attributes['pageModel'] = $GLOBALS['objPage']->id;
        }

        $fragmentIdentifier = 'contao.frontend_module.' . $type;

        $options = $this->fragmentRegistry->getOptions($fragmentIdentifier);
        $fragment = $this->fragmentRegistry->getFragment($fragmentIdentifier);
        $request = $this->requestStack->getCurrentRequest();

        if ($fragment instanceof SimpleRenderingInformationProvidingInterface) {
            $attributes = $fragment->getControllerRequestAttributes($request, $attributes);
            $query = $fragment->getControllerRequestAttributes($request, $query);
        }

        $controllerReference = new ControllerReference(
            $options['controller'],
            $attributes,
            $query
        );

        $renderStrategy = $options['renderStrategy'] ?: 'inline';

        return $this->fragmentHandler->render($controllerReference, $renderStrategy);
    }
}
