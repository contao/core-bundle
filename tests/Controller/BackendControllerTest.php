<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Controller;

use Contao\CoreBundle\Controller\BackendController;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\CoreBundle\Picker\PickerInterface;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BackendControllerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $controller = new BackendController();

        $this->assertInstanceOf('Contao\CoreBundle\Controller\BackendController', $controller);
    }

    public function testReturnsAResponseInTheActionMethods(): void
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        /** @var ContainerInterface $container */
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

    public function testReturnsAResponseInThePickerActionMethod(): void
    {
        $picker = $this->createMock(PickerInterface::class);

        $picker
            ->method('getCurrentUrl')
            ->willReturn('/foobar')
        ;

        $builder = $this->createMock(PickerBuilderInterface::class);

        $builder
            ->method('create')
            ->willReturn($picker)
        ;

        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->willReturn($builder)
        ;

        $controller = new BackendController();
        $controller->setContainer($container);

        $request = new Request();
        $request->query->set('context', 'page');
        $request->query->set('extras', ['fieldType' => 'radio']);
        $request->query->set('value', '{{link_url::5}}');

        $response = $controller->pickerAction($request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('/foobar', $response->getTargetUrl());
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testDoesNotReturnAResponseInThePickerActionMethodIfThePickerExtrasAreInvalid(): void
    {
        $controller = new BackendController();

        $request = new Request();
        $request->query->set('extras', null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid picker extras');

        $controller->pickerAction($request);
    }

    public function testDoesNotReturnAResponseInThePickerActionMethodIfThePickerContextIsUnsupported(): void
    {
        $builder = $this->createMock(PickerBuilderInterface::class);

        $builder
            ->method('create')
            ->willReturn(null)
        ;

        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->willReturn($builder)
        ;

        $controller = new BackendController();
        $controller->setContainer($container);

        $request = new Request();
        $request->query->set('context', 'invalid');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Unsupported picker context');

        $controller->pickerAction($request);
    }
}
