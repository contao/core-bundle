<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Fragment;

use Contao\CoreBundle\Fragment\FragmentConfig;
use Contao\CoreBundle\Fragment\FragmentPreHandlerInterface;
use Contao\CoreBundle\Fragment\FragmentRegistry;
use Contao\CoreBundle\Fragment\FragmentRenderer;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Contao\CoreBundle\Fragment\UnknownFragmentException;
use Contao\PageModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class FragmentRendererTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $renderer = new FragmentRenderer(
            new FragmentRegistry(),
            $this->createMock(FragmentHandler::class),
            new ServiceLocator([])
        );

        $this->assertInstanceOf('Contao\CoreBundle\Fragment\FragmentRenderer', $renderer);
        $this->assertInstanceOf('Contao\CoreBundle\Fragment\FragmentRendererInterface', $renderer);
    }

    public function testThrowsAnExceptionIfTheFragmentNameIsInvalid()
    {
        $this->expectException(UnknownFragmentException::class);

        $renderer = new FragmentRenderer(
            new FragmentRegistry(),
            $this->createMock(FragmentHandler::class),
            new ServiceLocator([])
        );

        $renderer->render(new FragmentReference('foo.bar'));
    }

    public function testPassesRendererToTheFragmentHandler()
    {
        $registry = new FragmentRegistry();
        $uri = new FragmentReference('foo.bar');

        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));
        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'inline'));
        $handler->expects($this->once())->method('render')->with($uri, 'inline');
        $renderer->render($uri);

        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));
        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'esi'));
        $handler->expects($this->once())->method('render')->with($uri, 'esi');
        $renderer->render($uri);
    }

    public function testPassesOptionsToTheFragmentHandler()
    {
        $registry = new FragmentRegistry();
        $uri = new FragmentReference('foo.bar');

        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));
        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'inline', ['foo' => 'bar']));
        $handler->expects($this->once())->method('render')->with($uri, 'inline', ['foo' => 'bar']);
        $renderer->render($uri);

        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));
        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'inline', ['bar' => 'baz']));
        $handler->expects($this->once())->method('render')->with($uri, 'inline', ['bar' => 'baz']);
        $renderer->render($uri);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddsPageIdFromGlobals()
    {
        $registry = new FragmentRegistry();
        $uri = new FragmentReference('foo.bar');
        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));

        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'inline', ['foo' => 'bar']));

        $handler
            ->expects($this->once())
            ->method('render')
            ->with($this->callback(function () use ($uri) {
                return isset($uri->attributes['pageModel']) && $uri->attributes['pageModel'] === 42;
            }))
        ;

        $GLOBALS['objPage'] = new PageModel();
        $GLOBALS['objPage']->id = 42;

        $renderer->render($uri);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoesNotOverridePageIdInAttributes()
    {
        $registry = new FragmentRegistry();
        $uri = new FragmentReference('foo.bar', ['pageModel' => 99]);
        $handler = $this->createMock(FragmentHandler::class);
        $renderer = new FragmentRenderer($registry, $handler, new ServiceLocator([]));

        $registry->add('foo.bar', new FragmentConfig('foo::bar', 'inline', ['foo' => 'bar']));

        $handler
            ->expects($this->once())
            ->method('render')
            ->with($this->callback(function () use ($uri) {
                return isset($uri->attributes['pageModel']) && $uri->attributes['pageModel'] === 99;
            }))
        ;

        $GLOBALS['objPage'] = new PageModel();
        $GLOBALS['objPage']->id = 42;

        $renderer->render($uri);
    }

    public function testCallsPreHandlers()
    {
        $registry = new FragmentRegistry();
        $prehandler = $this->createMock(FragmentPreHandlerInterface::class);
        $serviceLocator = $this->createMock(ServiceLocator::class);
        $renderer = new FragmentRenderer($registry, $this->createMock(FragmentHandler::class), $serviceLocator);

        $uri = new FragmentReference('foo.bar');
        $config = new FragmentConfig('foo::bar', 'inline', ['foo' => 'bar']);

        $registry->add('foo.bar', $config);

        $serviceLocator
            ->expects($this->once())
            ->method('has')
            ->with('foo.bar')
            ->willReturn(true)
        ;

        $serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with('foo.bar')
            ->willReturn($prehandler)
        ;

        $prehandler
            ->expects($this->once())
            ->method('preHandleFragment')
            ->with($uri, $config)
        ;

        $renderer->render($uri);
    }
}
