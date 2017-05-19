<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Controller;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\VersionConflictController;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Tests\LanguageHelper;
use Contao\Environment;
use Contao\System;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Tests the VersionConflictController class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class VersionConflictControllerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        $GLOBALS['TL_LANGUAGE'] = 'en';
        $GLOBALS['TL_LANG']['MSC'] = new LanguageHelper();
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        unset($GLOBALS['TL_LANGUAGE']);
        unset($GLOBALS['TL_LANG']);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $controller = new VersionConflictController();

        $this->assertInstanceOf('Contao\CoreBundle\Controller\VersionConflictController', $controller);
    }

    /**
     * Tests the indexAction() method.
     */
    public function testIndexAction()
    {
        $request = new Request();
        $request->query->set('theirs', 2);
        $request->query->set('mine', 1);
        $request->query->set('table', 'tl_content');
        $request->query->set('id', 42);

        $controller = new VersionConflictController();
        $controller->setContainer($this->mockContainer('?do=page&id=1'));

        $response = $controller->indexAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('?do=page&id=1', $response->getContent());
    }

    /**
     * Tests the indexAction() method without a referer.
     */
    public function testIndexActionWithoutReferer()
    {
        $request = new Request();
        $request->query->set('theirs', 2);
        $request->query->set('mine', 1);
        $request->query->set('table', 'tl_content');
        $request->query->set('id', 42);

        $controller = new VersionConflictController();
        $controller->setContainer($this->mockContainer());

        $response = $controller->indexAction($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('', $response->getContent());
    }

    /**
     * Mocks the container object.
     *
     * @param string|null $query
     *
     * @return Container
     */
    private function mockContainer($query = null)
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->method('has')
            ->willReturn(null !== $query)
        ;

        $session
            ->method('get')
            ->willReturn('http://localhost/contao'.$query)
        ;

        $router = $this->createMock(RouterInterface::class);

        $router
            ->method('generate')
            ->willReturn('http://localhost/contao')
        ;

        $template = $this->createMock(BackendTemplate::class);

        $template
            ->expects($this->at(0))
            ->method('__set')
            ->with('language', 'en')
        ;

        $template
            ->expects($this->at(1))
            ->method('__set')
            ->with('h1')
        ;

        $template
            ->expects($this->at(2))
            ->method('__set')
            ->with('back')
        ;

        $template
            ->expects($this->at(3))
            ->method('__set')
            ->with('title')
        ;

        $template
            ->expects($this->at(4))
            ->method('__set')
            ->with('theme', 'flexible')
        ;

        $template
            ->expects($this->at(5))
            ->method('__set')
            ->with('charset', 'utf-8')
        ;

        $template
            ->expects($this->at(6))
            ->method('__set')
            ->with('base', 'http://localhost')
        ;

        $template
            ->expects($this->at(7))
            ->method('__set')
            ->with('explain1')
        ;

        $template
            ->expects($this->at(8))
            ->method('__set')
            ->with('explain2')
        ;

        $template
            ->expects($this->at(9))
            ->method('__set')
            ->with('href', 'http://localhost/contao'.$query)
        ;

        $template
            ->expects($this->at(10))
            ->method('__set')
            ->with('diff', 'foobar')
        ;

        $template
            ->method('getResponse')
            ->willReturn(new Response($query))
        ;

        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $framework
            ->method('createInstance')
            ->willReturn($template)
        ;

        $framework
            ->method('getAdapter')
            ->willReturnCallback(function ($adapter) {
                switch ($adapter) {
                    case System::class:
                        return $this->mockSystemAdapter();

                    case Controller::class:
                        return $this->mockControllerAdapter();

                    case Backend::class:
                        return $this->mockBackendAdapter();

                    case Config::class:
                        return $this->mockConfigAdapter();

                    case Environment::class:
                        return $this->mockEnvironmentAdapter();
                }
            });

        $container = new Container();
        $container->set('session', $session);
        $container->set('router', $router);
        $container->set('contao.framework', $framework);

        return $container;
    }

    /**
     * Mocks the System class adapter.
     *
     * @return System|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockSystemAdapter()
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLanguageFile'])
            ->getMockForAbstractClass()
        ;

        $adapter
            ->expects($this->atLeastOnce())
            ->method('loadLanguageFile')
        ;

        return $adapter;
    }

    /**
     * Mocks the Controller class adapter.
     *
     * @return Controller|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockControllerAdapter()
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStaticUrls'])
            ->getMockForAbstractClass()
        ;

        $adapter
            ->expects($this->atLeastOnce())
            ->method('setStaticUrls')
        ;

        return $adapter;
    }

    /**
     * Mocks the Backend class adapter.
     *
     * @return Backend|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockBackendAdapter()
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTheme'])
            ->getMockForAbstractClass()
        ;

        $adapter
            ->method('getTheme')
            ->willReturn('flexible')
        ;

        return $adapter;
    }

    /**
     * Mocks the Config class adapter.
     *
     * @return Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockConfigAdapter()
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock()
        ;

        $adapter
            ->method('get')
            ->willReturn('utf-8')
        ;

        return $adapter;
    }

    /**
     * Mocks the Environment class adapter.
     *
     * @return Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockEnvironmentAdapter()
    {
        $adapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass()
        ;

        $adapter
            ->method('get')
            ->willReturn('http://localhost')
        ;

        return $adapter;
    }
}
