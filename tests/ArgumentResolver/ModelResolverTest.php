<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
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
 * Class ArgumentResolverTest
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

    public function testFrameworkNotInstantiatedWhenRequestAttributeNotPresent()
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);
        $framework->expects($this->never())->method('initialize');

        $request = Request::create('/foobar');
        $argument = new ArgumentMetadata('foobar', 'string', false, false, '');
        $resolver = new ModelResolver($framework);
        $resolver->supports($request, $argument);
    }

    public function testSupportsReturnsFalseIfWrongType()
    {
        $framework = $this->createMock(ContaoFrameworkInterface::class);
        $framework->expects($this->once())->method('initialize');

        $request = Request::create('/foobar');
        $request->attributes->set('foobar', 'test');
        $argument = new ArgumentMetadata('foobar', 'string', false, false, '');
        $resolver = new ModelResolver($framework);

        $this->assertFalse($resolver->supports($request, $argument));
    }

    public function testResolve()
    {
         $adapter = $this
             ->getMockBuilder(Adapter::class)
             ->disableOriginalConstructor()
             ->setMethods(['findByPk'])
             ->getMock();

        $adapter
            ->expects($this->once())
            ->method('findByPk')
            ->willReturn('testReturn');

        $framework = $this->mockContaoFramework(
            null,
            null,
            ['foobar' => $adapter]
        );

        $request = Request::create('/foobar');
        $request->attributes->set('foobar', 42);
        $argument = new ArgumentMetadata('foobar', 'foobar', false, false, '');

        $resolver = new ModelResolver($framework);
        $generator = $resolver->resolve($request, $argument);

        $this->assertInstanceOf(\Generator::class, $generator);

        foreach ($generator as $returnValue) {
            $this->assertSame('testReturn', $returnValue);
        }
    }
}
