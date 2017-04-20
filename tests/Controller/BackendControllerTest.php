<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Controller;

use Contao\CoreBundle\Controller\BackendController;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the BackendControllerTest class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BackendControllerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $controller = new BackendController();

        $this->assertInstanceOf('Contao\CoreBundle\Controller\BackendController', $controller);
    }

    /**
     * Tests the controller actions.
     */
    public function testActions()
    {
        $framework = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\ContaoFramework')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        /** @var ContainerBuilder $container */
        $container = $this->mockKernel()->getContainer();
        $container->set('contao.framework', $framework);

        $controller = new BackendController();
        $controller->setContainer($container);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->mainAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->loginAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->passwordAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->previewAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->confirmAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->fileAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->helpAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->pageAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->popupAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->switchAction());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $controller->alertsAction());
    }
}
