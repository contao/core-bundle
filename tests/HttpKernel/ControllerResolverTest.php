<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\HttpKernel;

use Contao\CoreBundle\Fragment\FragmentConfig;
use Contao\CoreBundle\Fragment\FragmentRegistry;
use Contao\CoreBundle\HttpKernel\ControllerResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolverTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $resolver = new ControllerResolver(
            $this->createMock(ControllerResolverInterface::class),
            new FragmentRegistry()
        );

        $this->assertInstanceOf('Contao\CoreBundle\HttpKernel\ControllerResolver', $resolver);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface', $resolver);
    }

    public function testSetControllerAttributeFromFragmentRegistry()
    {
        $request = new Request();
        $registry = $this->createMock(FragmentRegistry::class);
        $resolver = new ControllerResolver($this->createMock(ControllerResolverInterface::class), $registry);
        $config = new FragmentConfig('Foo\Bar\FooBarController');

        $registry
            ->expects($this->once())
            ->method('get')
            ->with('foo.bar')
            ->willReturn($config)
        ;

        $request->attributes->set('_controller', 'foo.bar');

        $resolver->getController($request);

        $this->assertSame('Foo\Bar\FooBarController', $request->attributes->get('_controller'));
    }

    public function testForwardsControllerToDecoratedClass()
    {
        $decorated = $this->createMock(ControllerResolverInterface::class);
        $resolver = new ControllerResolver($decorated, new FragmentRegistry());

        $decorated
            ->expects($this->once())
            ->method('getController')
        ;

        $resolver->getController(new Request());
    }

    public function testForwardsArgumentsToDecoratedClass()
    {
        $decorated = $this->createMock(ControllerResolverInterface::class);
        $resolver = new ControllerResolver($decorated, new FragmentRegistry());

        $decorated
            ->expects($this->once())
            ->method('getArguments')
        ;

        $resolver->getArguments(new Request(), '');
    }
}
