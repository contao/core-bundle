<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\ArgumentResolver;

use Contao\CoreBundle\ArgumentResolver\ModelResolver;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Tests\TestCase;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Class ArgumentResolverTest.
 *
 * @autor Yanick Witschi
 */
class ModelResolverTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $resolver = new ModelResolver($this->mockContaoFramework());

        $this->assertInstanceOf('Contao\CoreBundle\ArgumentResolver\ModelResolver', $resolver);
    }

    public function testResolvesTheModel()
    {
        $pageModel = new PageModel();
        $pageModel->setRow(['id' => 42]);

        $adapter = $this->createMock(Adapter::class);

        $adapter
            ->expects($this->once())
            ->method('__call')
            ->with('findByPk', [42])
            ->willReturn($pageModel)
        ;

        $framework = $this->mockContaoFramework(
            null,
            null,
            [PageModel::class => $adapter]
        );

        $request = Request::create('/foobar');
        $request->attributes->set('pageModel', 42);

        $resolver = new ModelResolver($framework);

        $generator = $resolver->resolve(
            $request,
            new ArgumentMetadata('pageModel', PageModel::class, false, false, '')
        );

        $this->assertInstanceOf(\Generator::class, $generator);

        foreach ($generator as $resolved) {
            $this->assertSame($pageModel, $resolved);
        }
    }

    public function testChecksIfTheRequestAttributeExists()
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->never())
            ->method('initialize')
        ;

        $request = Request::create('/foobar');
        $argument = new ArgumentMetadata('foobar', 'string', false, false, '');

        $resolver = new ModelResolver($framework);
        $resolver->supports($request, $argument);
    }

    public function testChecksIfTheArgumentTypeIsCorrect()
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->once())
            ->method('initialize')
        ;

        $request = Request::create('/foobar');
        $request->attributes->set('foobar', 'test');

        $argument = new ArgumentMetadata('foobar', 'string', false, false, '');
        $resolver = new ModelResolver($framework);

        $this->assertFalse($resolver->supports($request, $argument));
    }

    public function testSupportsNullableArguments()
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->once())
            ->method('initialize')
        ;

        $request = Request::create('/foobar');
        $request->attributes->set('pageModel', 42);

        $argument = new ArgumentMetadata('pageModel', PageModel::class, false, false, '', true);
        $resolver = new ModelResolver($framework);

        $this->assertTrue($resolver->supports($request, $argument));
    }

    public function testChecksIfTheModelExistsIfTheArgumentIsNotNullable()
    {
        $adapter = $this->createMock(Adapter::class);

        $adapter
            ->expects($this->once())
            ->method('__call')
            ->with('findByPk', [42])
            ->willReturn(null)
        ;

        $framework = $this->mockContaoFramework(
            null,
            null,
            [PageModel::class => $adapter]
        );

        $request = Request::create('/foobar');
        $request->attributes->set('pageModel', 42);

        $argument = new ArgumentMetadata('pageModel', PageModel::class, false, false, '');
        $resolver = new ModelResolver($framework);

        $this->assertFalse($resolver->supports($request, $argument));
    }
}
