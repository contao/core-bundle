<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\ModuleModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FrontendModuleController
 *
 * @author Yanick Witschi <https://github.com/toflar>
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class FrontendModuleController extends Controller implements ArgumentConverterInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ControllerReference[]
     */
    private $controllers = [];

    /**
     * FrontendModuleController constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return ControllerReference[]
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @return ControllerReference[]|null
     */
    public function getController($type)
    {
        return $this->controllers[$type];
    }

    /**
     * @param string              $type
     * @param ControllerReference $controller
     *
     * @return FrontendModuleController
     */
    public function setController($type, ControllerReference $controller)
    {
        $this->controllers[$type] = $controller;

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/_contao/frontend_module", name="contao_frontend_module")
     */
    public function forwardAction(ModuleModel $moduleModel)
    {
        // Legacy support
        foreach ($GLOBALS['FE_MOD'] as $groupName => $group) {
            foreach ($group as $moduleType => $moduleClass) {
                // Controllers are always more important than legacy
                if (null === $this->getController($moduleType)) {
                    // TODO: Trigger deprecated notice
                    $this->setController($moduleType,
                        new ControllerReference('contao.controller.frontend_module.legacy:indexAction')
                    );
                }
            }
        }

        if (!isset($this->controllers[$moduleModel->type])) {
            throw new NotFoundHttpException(sprintf(
                'There\'s no controller for front end module type "%s".',
                $moduleModel->type
            ));
        }

        /** @var ControllerReference $controllerRef */
        $controllerRef = $this->controllers[$moduleModel->type];

        // Pass on the module id
        $controllerRef->query['feModId'] = $moduleModel->id;

        return $this->forward(
            $controllerRef->controller,
            [],
            $controllerRef->query
        );
    }

    /**
     * Convert arguments on the request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function convertArguments(Request $request)
    {
        if (!$request->query->has('feModId')) {
            return;
        }

        // Set inColumn which is "main" by default.
        $request->attributes->set('inColumn', $request->query->get('inColumn', 'main'));

        $moduleModel = $this->framework->getAdapter('Contao\ModuleModel')
                            ->findByPk($request->query->getInt('feModId'));

        if (null !== $moduleModel) {
            $request->attributes->set('moduleModel', $moduleModel);
        }
    }
}
