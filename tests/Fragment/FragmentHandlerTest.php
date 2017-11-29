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
use Contao\CoreBundle\Fragment\FragmentHandler;
use Contao\CoreBundle\Fragment\FragmentPreHandlerInterface;
use Contao\CoreBundle\Fragment\FragmentRegistry;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Contao\CoreBundle\Fragment\UnknownFragmentException;
use Contao\PageModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler as BaseFragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

class FragmentHandlerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $fragmentHandler = $this->createInstance();

        $this->assertInstanceOf('Contao\CoreBundle\Fragment\FragmentHandler', $fragmentHandler);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Fragment\FragmentHandler', $fragmentHandler);
    }

    public function testThrowsAnExceptionIfTheFragmentNameIsInvalid(): void
    {
        $fragmentHandler = $this->createInstance();

        $this->expectException(UnknownFragmentException::class);

        $fragmentHandler->render(new FragmentReference('foo.bar'));
    }

    /**
     * @param string $renderingStrategy
     *
     * @dataProvider getRenderingStrategies
     */
    public function testPassesTheRendererToTheRenderer(string $renderingStrategy): void
    {
        $uri = new FragmentReference('foo.bar');

        $registry = new FragmentRegistry();
        $registry->add('foo.bar', new FragmentConfig('foo.bar', $renderingStrategy));

        $request = new Request();
        $renderers = $this->mockServiceLocatorWithRenderer($renderingStrategy, [$uri, $request, ['ignore_errors' => false]]);

        $renderer = $this->createInstance($registry, $renderers, null, $request);
        $renderer->render($uri);
    }

    /**
     * @return array
     */
    public function getRenderingStrategies(): array
    {
        return [['inline'], ['esi']];
    }

    /**
     * @param array $options
     *
     * @dataProvider getOptions
     */
    public function testPassesTheOptionsToTheRenderer(array $options): void
    {
        $uri = new FragmentReference('foo.bar');

        $registry = new FragmentRegistry();
        $registry->add('foo.bar', new FragmentConfig('foo.bar', 'inline', $options));

        $request = new Request();
        $renderers = $this->mockServiceLocatorWithRenderer('inline', [$uri, $request, $options + ['ignore_errors' => false]]);

        $renderer = $this->createInstance($registry, $renderers, null, $request);
        $renderer->render($uri);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            [['foo' => 'bar']],
            [['bar' => 'baz']],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAddsThePageIdFromTheGlobalPageObject(): void
    {
        $uri = new FragmentReference('foo.bar');

        $registry = new FragmentRegistry();
        $registry->add('foo.bar', new FragmentConfig('foo.bar', 'inline', ['foo' => 'bar']));

        $renderers = $this->mockServiceLocatorWithRenderer('inline', [$this->callback(
            function () use ($uri) {
                return isset($uri->attributes['pageModel']) && 42 === $uri->attributes['pageModel'];
            }
        )]);

        $GLOBALS['objPage'] = new PageModel();
        $GLOBALS['objPage']->id = 42;

        $fragmentHandler = $this->createInstance($registry, $renderers);
        $fragmentHandler->render($uri);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoesNotOverrideAGivenPageId(): void
    {
        $uri = new FragmentReference('foo.bar', ['pageModel' => 99]);

        $registry = new FragmentRegistry();
        $registry->add('foo.bar', new FragmentConfig('foo.bar', 'inline', ['foo' => 'bar']));

        $renderers = $this->mockServiceLocatorWithRenderer('inline', [$this->callback(
            function () use ($uri) {
                return isset($uri->attributes['pageModel']) && 99 === $uri->attributes['pageModel'];
            }
        )]);

        $GLOBALS['objPage'] = new PageModel();
        $GLOBALS['objPage']->id = 42;

        $fragmentHandler = $this->createInstance($registry, $renderers);
        $fragmentHandler->render($uri);
    }

    public function testExecutesThePreHandlers(): void
    {
        $uri = new FragmentReference('foo.bar');
        $config = new FragmentConfig('foo.bar', 'inline', ['foo' => 'bar']);

        $registry = new FragmentRegistry();
        $registry->add('foo.bar', $config);

        $prehandler = $this->createMock(FragmentPreHandlerInterface::class);

        $prehandler
            ->expects($this->once())
            ->method('preHandleFragment')
            ->with($uri, $config)
        ;

        $preHandlers = $this->mockServiceLocator('foo.bar', $prehandler);
        $renderers = $this->mockServiceLocatorWithRenderer('inline');

        $fragmentHandler = $this->createInstance($registry, $renderers, $preHandlers);
        $fragmentHandler->render($uri);
    }

    private function createInstance(FragmentRegistry $registry = null, ServiceLocator $renderers = null, ServiceLocator $preHandlers = null, Request $request = null)
    {
        $registry = $registry ?: new FragmentRegistry();
        $renderers = $renderers ?: new ServiceLocator([]);
        $preHandlers = $preHandlers ?: new ServiceLocator([]);

        $requestStack = new RequestStack();
        $requestStack->push($request ?: new Request());

        return new FragmentHandler(
            $renderers,
            $this->createMock(BaseFragmentHandler::class),
            $requestStack,
            $registry,
            $preHandlers,
            true
        );
    }

    private function mockServiceLocatorWithRenderer(string $name, array $with = null)
    {
        $renderer = $this->createMock(FragmentRendererInterface::class);

        $renderer
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name)
        ;

        $method = $renderer->expects($this->once())->method('render');

        if (null !== $with) {
            $method = call_user_func_array([$method, 'with'], $with);
        }

        $method->willReturn(new Response());

        return $this->mockServiceLocator($name, $renderer);
    }

    private function mockServiceLocator(string $name, $service)
    {
        $serviceLocator = $this->createMock(ServiceLocator::class);

        $serviceLocator
            ->expects($this->once())
            ->method('has')
            ->with($name)
            ->willReturn(true)
        ;

        $serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($service)
        ;

        return $serviceLocator;
    }
}
