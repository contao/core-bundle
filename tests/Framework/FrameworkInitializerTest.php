<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Framework;

use Contao\Config;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * Tests the FrameworkInitializer class.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Dominik Tomasi <https://github.com/dtomasi>
 *
 * @preserveGlobalState disabled
 */
class FrameworkInitializerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $framework = $this->mockFrameworkInitializer();

        $this->assertInstanceOf('Contao\CoreBundle\Framework\FrameworkInitializer', $framework);
    }

    /**
     * Tests initializing the framework with a front end request.
     *
     * @runInSeparateProcess
     */
    public function testFrontendRequest()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_FRONTEND);
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/index.html')
        );

        $initializer->setContainer($container);
        $initializer->initialize();

        $this->assertTrue(defined('TL_MODE'));
        $this->assertTrue(defined('TL_START'));
        $this->assertTrue(defined('TL_ROOT'));
        $this->assertTrue(defined('TL_REFERER_ID'));
        $this->assertTrue(defined('TL_SCRIPT'));
        $this->assertFalse(defined('BE_USER_LOGGED_IN'));
        $this->assertFalse(defined('FE_USER_LOGGED_IN'));
        $this->assertTrue(defined('TL_PATH'));
        $this->assertEquals('FE', TL_MODE);
        $this->assertEquals($this->getRootDir(), TL_ROOT);
        $this->assertEquals('', TL_REFERER_ID);
        $this->assertEquals('index.html', TL_SCRIPT);
        $this->assertEquals('', TL_PATH);
        $this->assertEquals('en', $GLOBALS['TL_LANGUAGE']);
        $this->assertInstanceOf('Contao\CoreBundle\Session\Attribute\ArrayAttributeBag', $_SESSION['BE_DATA']);
        $this->assertInstanceOf('Contao\CoreBundle\Session\Attribute\ArrayAttributeBag', $_SESSION['FE_DATA']);
    }

    /**
     * Tests initializing the framework with a back end request.
     *
     * @runInSeparateProcess
     */
    public function testBackendRequest()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_contao_referer_id', 'foobar');
        $request->setLocale('de');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->initialize();

        $this->assertTrue(defined('TL_MODE'));
        $this->assertTrue(defined('TL_START'));
        $this->assertTrue(defined('TL_ROOT'));
        $this->assertTrue(defined('TL_REFERER_ID'));
        $this->assertTrue(defined('TL_SCRIPT'));
        $this->assertTrue(defined('BE_USER_LOGGED_IN'));
        $this->assertTrue(defined('FE_USER_LOGGED_IN'));
        $this->assertTrue(defined('TL_PATH'));
        $this->assertEquals('BE', TL_MODE);
        $this->assertEquals($this->getRootDir(), TL_ROOT);
        $this->assertEquals('foobar', TL_REFERER_ID);
        $this->assertEquals('contao/install', TL_SCRIPT);
        $this->assertEquals('', TL_PATH);
        $this->assertEquals('de', $GLOBALS['TL_LANGUAGE']);
    }

    /**
     * Tests initializing the framework without a request.
     *
     * @runInSeparateProcess
     */
    public function testWithoutRequest()
    {
        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->set('request_stack', new RequestStack());

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->initialize();

        $this->assertTrue(defined('TL_MODE'));
        $this->assertTrue(defined('TL_START'));
        $this->assertTrue(defined('TL_ROOT'));
        $this->assertTrue(defined('TL_REFERER_ID'));
        $this->assertTrue(defined('TL_SCRIPT'));
        $this->assertTrue(defined('BE_USER_LOGGED_IN'));
        $this->assertTrue(defined('FE_USER_LOGGED_IN'));
        $this->assertTrue(defined('TL_PATH'));
        $this->assertEquals('BE', TL_MODE);
        $this->assertEquals($this->getRootDir(), TL_ROOT);
        $this->assertNull(TL_REFERER_ID);
        $this->assertEquals('console', TL_SCRIPT);
        $this->assertNull(TL_PATH);
    }

    /**
     * Tests initializing the framework without a scope.
     *
     * @runInSeparateProcess
     */
    public function testWithoutScope()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_contao_referer_id', 'foobar');

        $container = $this->mockContainerWithContaoScopes();
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->initialize();

        $this->assertTrue(defined('TL_MODE'));
        $this->assertTrue(defined('TL_START'));
        $this->assertTrue(defined('TL_ROOT'));
        $this->assertTrue(defined('TL_REFERER_ID'));
        $this->assertTrue(defined('TL_SCRIPT'));
        $this->assertFalse(defined('BE_USER_LOGGED_IN'));
        $this->assertFalse(defined('FE_USER_LOGGED_IN'));
        $this->assertTrue(defined('TL_PATH'));
        $this->assertNull(TL_MODE);
        $this->assertEquals($this->getRootDir(), TL_ROOT);
        $this->assertEquals('foobar', TL_REFERER_ID);
        $this->assertEquals('contao/install', TL_SCRIPT);
        $this->assertEquals('', TL_PATH);
    }

    /**
     * Tests that the error level will get updated when configured.
     *
     * @runInSeparateProcess
     */
    public function testErrorLevelOverride()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_contao_referer_id', 'foobar');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $errorReporting = error_reporting();
        error_reporting(E_ALL ^ E_USER_NOTICE);

        $this->assertNotEquals(
            $errorReporting,
            error_reporting(),
            'Test is invalid, error level has not changed.'
        );

        $initializer->setContainer($container);
        $initializer->initialize();

        $this->assertEquals($errorReporting, error_reporting());

        error_reporting($errorReporting);
    }

    /**
     * Tests initializing the framework with a valid request token.
     *
     * @runInSeparateProcess
     */
    public function testValidRequestToken()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_token_check', true);
        $request->setMethod('POST');
        $request->request->set('REQUEST_TOKEN', 'foobar');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->initialize();
    }

    /**
     * Tests initializing the framework with an invalid request token.
     *
     * @runInSeparateProcess
     * @expectedException \Contao\CoreBundle\Exception\InvalidRequestTokenException
     */
    public function testInvalidRequestToken()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_token_check', true);
        $request->setMethod('POST');
        $request->request->set('REQUEST_TOKEN', 'invalid');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

        $rtAdapter = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\Adapter')
            ->setMethods(['get', 'validate'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $rtAdapter
            ->expects($this->any())
            ->method('get')
            ->willReturn('nonsense')
        ;

        $rtAdapter
            ->expects($this->once())
            ->method('validate')
            ->willReturn(false)
        ;

        $initializer = $this->mockFrameworkInitializer($container->get('request_stack'));
        $initializer->setContainer($container);
        $initializer->setFramework($this->mockContaoFramework(['Contao\RequestToken' => $rtAdapter]));
        $initializer->initialize();
    }

    /**
     * Tests if the request token check is skipped if the attribute is false.
     *
     * @runInSeparateProcess
     */
    public function testRequestTokenCheckSkippedIfAttributeFalse()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');
        $request->attributes->set('_token_check', false);
        $request->setMethod('POST');
        $request->request->set('REQUEST_TOKEN', 'foobar');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

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
            ->expects($this->never())
            ->method('validate')
        ;

        $initializer = $this->mockFrameworkInitializer($container->get('request_stack'));
        $initializer->setContainer($container);
        $initializer->initialize();
    }

    /**
     * Tests initializing the framework with an incomplete installation.
     *
     * @runInSeparateProcess
     * @expectedException \Contao\CoreBundle\Exception\IncompleteInstallationException
     */
    public function testIncompleteInstallation()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_BACKEND);
        $container->get('request_stack')->push($request);

        $configAdapter = $this
            ->getMockBuilder('Contao\CoreBundle\Framework\Adapter')
            ->disableOriginalConstructor()
            ->setMethods(['isComplete', 'get', 'preload', 'getInstance'])
            ->getMock()
        ;

        $configAdapter
            ->expects($this->any())
            ->method('isComplete')
            ->willReturn(false)
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

        $initializer = $this->mockFrameworkInitializer(
            $container->get('request_stack'),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->setFramework($this->mockContaoFramework(['Contao\Config' => $configAdapter]));
        $initializer->initialize();
    }

    /**
     * Tests initializing the framework without a container.
     *
     * @runInSeparateProcess
     * @expectedException \LogicException
     */
    public function testContainerNotSet()
    {
        $initializer = $this->mockFrameworkInitializer(
            new RequestStack(),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer(null);
        $initializer->initialize();
    }

    /**
     * Tests initializing the framework without setting the framework in the initializer.
     *
     * @runInSeparateProcess
     * @expectedException \LogicException
     */
    public function testFrameworkNotSet()
    {
        $request = new Request();
        $request->attributes->set('_route', 'dummy');

        $container = $this->mockContainerWithContaoScopes();
        $container->enterScope(ContaoCoreBundle::SCOPE_FRONTEND);
        $container->get('request_stack')->push($request);

        $initializer = $this->mockFrameworkInitializer(
            new RequestStack(),
            $this->mockRouter('/contao/install')
        );

        $initializer->setContainer($container);
        $initializer->setFramework(null);
        $initializer->initialize();
    }
}
