<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Controller;

use Contao\CoreBundle\Controller\BackendController;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $controller = $this->mockBackendController();

        $this->assertInstanceOf('Contao\\CoreBundle\\Controller\\BackendController', $controller);
    }

    /**
     * Tests the controller actions.
     */
    public function testActions()
    {
        $controller = $this->mockBackendController();

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->mainAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->loginAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->installAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->passwordAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->previewAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->confirmAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->fileAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->helpAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->pageAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->popupAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->switchAction());
    }

    /**
     * Mocks the back end controller
     *
     * @return BackendController
     */
    private function mockBackendController()
    {
        $response = new Response();
        $controller = $this->getMock('Contao\\CoreBundle\\Controller\\BackendController');
        $controller->expects($this->any())->method('mainAction')->willReturn($response);
        $controller->expects($this->any())->method('loginAction')->willReturn($response);
        $controller->expects($this->any())->method('installAction')->willReturn($response);
        $controller->expects($this->any())->method('passwordAction')->willReturn($response);
        $controller->expects($this->any())->method('previewAction')->willReturn($response);
        $controller->expects($this->any())->method('confirmAction')->willReturn($response);
        $controller->expects($this->any())->method('fileAction')->willReturn($response);
        $controller->expects($this->any())->method('helpAction')->willReturn($response);
        $controller->expects($this->any())->method('pageAction')->willReturn($response);
        $controller->expects($this->any())->method('switchAction')->willReturn($response);

        return $controller;
    }
}
