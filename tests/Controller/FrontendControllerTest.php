<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Controller;

use Contao\CoreBundle\Controller\FrontendController;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the FrontendController class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FrontendControllerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $controller = $this->mockFrontendController();

        $this->assertInstanceOf('Contao\\CoreBundle\\Controller\\FrontendController', $controller);
    }

    /**
     * Tests the controller actions.
     */
    public function testActions()
    {
        $controller = $this->mockFrontendController();

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->indexAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->cronAction());
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $controller->shareAction());
    }

    /**
     * Mocks the front end controller
     *
     * @return FrontendController
     */
    private function mockFrontendController()
    {
        $response = new Response();
        $controller = $this->getMock('Contao\\CoreBundle\\Controller\\FrontendController');
        $controller->expects($this->any())->method('indexAction')->willReturn($response);
        $controller->expects($this->any())->method('cronAction')->willReturn($response);
        $controller->expects($this->any())->method('shareAction')->willReturn($response);

        return $controller;
    }
}
