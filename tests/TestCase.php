<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\FrameworkInitializer;
use Contao\CoreBundle\Session\Attribute\ArrayAttributeBag;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Abstract TestCase class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns the path to the fixtures directory.
     *
     * @return string The root directory path
     */
    public function getRootDir()
    {
        return __DIR__ . '/Fixtures';
    }

    /**
     * Returns the path to the fixtures cache directory.
     *
     * @return string The cache directory path
     */
    public function getCacheDir()
    {
        return $this->getRootDir() . '/app/cache';
    }

    /**
     * Mocks a Contao kernel.
     *
     * @return Kernel|\PHPUnit_Framework_MockObject_MockObject The kernel object
     */
    protected function mockKernel()
    {
        $kernel = $this->getMock(
            'Symfony\Component\HttpKernel\Kernel',
            [
                // KernelInterface
                'registerBundles',
                'registerContainerConfiguration',
                'boot',
                'shutdown',
                'getBundles',
                'isClassInActiveBundle',
                'getBundle',
                'locateResource',
                'getName',
                'getEnvironment',
                'isDebug',
                'getRootDir',
                'getContainer',
                'getStartTime',
                'getCacheDir',
                'getLogDir',
                'getCharset',

                // HttpKernelInterface
                'handle',

                // Serializable
                'serialize',
                'unserialize',
            ],
            ['test', false]
        );

        $container = $this->mockContainerWithContaoScopes();

        $kernel
            ->expects($this->any())
            ->method('getContainer')
            ->willReturn($container)
        ;

        return $kernel;
    }

    /**
     * Mocks a router returning the given URL.
     *
     * @param string $url The URL to return
     *
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject The router object
     */
    protected function mockRouter($url)
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $router
            ->expects($this->any())
            ->method('generate')
            ->willReturn($url)
        ;

        return $router;
    }

    /**
     * Mocks a CSRF token manager.
     *
     * @return CsrfTokenManagerInterface|\PHPUnit_Framework_MockObject_MockObject The token manager object
     */
    protected function mockTokenManager()
    {
        $tokenManager = $this
            ->getMockBuilder('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface')
            ->setMethods(['getToken'])
            ->getMockForAbstractClass()
        ;

        $tokenManager
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(new CsrfToken('_csrf', 'testValue'))
        ;

        $tokenManager
            ->expects($this->any())
            ->method('refreshToken')
            ->willReturn(new CsrfToken('_csrf', 'testValue'))
        ;

        return $tokenManager;
    }

    /**
     * Mocks a Symfony session containing the Contao attribute bags.
     *
     * @return SessionInterface The session object
     */
    protected function mockSession()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->setId('session_test');
        $session->start();

        $beBag = new ArrayAttributeBag('_contao_be_attributes');
        $beBag->setName('contao_backend');

        $session->registerBag($beBag);

        $feBag = new ArrayAttributeBag('_contao_fe_attributes');
        $feBag->setName('contao_frontend');

        $session->registerBag($feBag);

        return $session;
    }

    /**
     * Mocks a container with scopes.
     *
     * @return Container|\PHPUnit_Framework_MockObject_MockObject The container object
     */
    protected function mockContainerWithContaoScopes()
    {
        $container = new Container();
        $container->addScope(new Scope(ContaoCoreBundle::SCOPE_BACKEND));
        $container->addScope(new Scope(ContaoCoreBundle::SCOPE_FRONTEND));
        $container->setParameter('kernel.root_dir', $this->getRootDir());
        $container->setParameter('kernel.cache_dir', $this->getCacheDir());
        $container->setParameter('kernel.debug', false);
        $container->setParameter('contao.image.bypass_cache', false);
        $container->setParameter('contao.image.target_path', 'assets/images');

        $container->set(
            'contao.resource_finder',
            new ResourceFinder($this->getRootDir() . '/vendor/contao/test-bundle/Resources/contao')
        );

        $container->set(
            'contao.resource_locator',
            new FileLocator($this->getRootDir() . '/vendor/contao/test-bundle/Resources/contao')
        );

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '123.456.789.0');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container->set('request_stack', $requestStack);
        $container->set('session', $this->mockSession());

        return $container;
    }

    /**
     * Returns a ContaoFramework instance.
     *
     * @param array $adapters An optional array of adapters
     *
     * @return ContaoFramework|\PHPUnit_Framework_MockObject_MockObject The object instance
     */
    public function mockContaoFramework(array $adapters = [])
    {
        if (!isset($adapters['Contao\Config'])) {
            $adapters['Contao\Config'] = $this->mockConfigAdapter();
        }

        if (!isset($adapters['Contao\RequestToken'])) {
            $adapters['Contao\RequestToken'] = $this->mockRequestTokenAdapter();
        }

        /** @var ContaoFramework|\PHPUnit_Framework_MockObject_MockObject $framework */
        $framework = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\ContaoFramework')
            ->setMethods(['getAdapter'])
            ->getMock()
        ;

        $framework
            ->expects($this->any())
            ->method('getAdapter')
            ->willReturnCallback(function ($key) use ($adapters) {
                return $adapters[$key];
            })
        ;

        return $framework;
    }

    /**
     * Returns a FrameworkInitializer instance.
     *
     * @param RequestStack    $requestStack The request stack
     * @param RouterInterface $router       The router object
     *
     * @return FrameworkInitializer The object instance
     */
    public function mockFrameworkInitializer(RequestStack $requestStack = null, RouterInterface $router = null)
    {
        $container = $this->mockContainerWithContaoScopes();

        if (null === $requestStack) {
            $requestStack = $container->get('request_stack');
        }

        if (null === $router) {
            $router = $this->mockRouter('/index.html');
        }

        $initializer = new FrameworkInitializer(
            $requestStack,
            $router,
            $this->mockSession(),
            $this->getRootDir() . '/app',
            error_reporting()
        );

        $initializer->setContainer($container);
        $initializer->setFramework($this->mockContaoFramework());

        return $initializer;
    }

    /**
     * Mocks a config adapter.
     *
     * @return Adapter|\PHPUnit_Framework_MockObject_MockObject The config adapter
     */
    protected function mockConfigAdapter()
    {
        $configAdapter = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\Adapter')
            ->setMethods(['isComplete', 'preload', 'getInstance', 'get'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $configAdapter
            ->expects($this->any())
            ->method('isComplete')
            ->willReturn(true)
        ;

        $configAdapter
            ->expects($this->any())
            ->method('preload')
            ->willReturn(null)
        ;

        $configAdapter
            ->expects($this->any())
            ->method('getInstance')
            ->willReturn(null)
        ;

        $configAdapter
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case 'characterSet':
                        return 'UTF-8';

                    case 'timeZone':
                        return 'Europe/Berlin';

                    default:
                        return null;
                }
            })
        ;

        return $configAdapter;
    }

    /**
     * Mocks a request token adapter.
     *
     * @return Adapter|\PHPUnit_Framework_MockObject_MockObject The request token adapter
     */
    protected function mockRequestTokenAdapter()
    {
        $rtAdapter = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\Adapter')
            ->setMethods(['get', 'validate'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $rtAdapter
            ->expects($this->any())
            ->method('get')
            ->willReturn('foobar')
        ;

        $rtAdapter
            ->expects($this->any())
            ->method('validate')
            ->willReturn(true)
        ;

        return $rtAdapter;
    }
}
