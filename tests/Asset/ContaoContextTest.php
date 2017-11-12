<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Asset;

use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Tests\TestCase;
use Contao\Model\Registry;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContaoContextTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        unset($GLOBALS['objPage']);
    }

    public function testCanBeInstantiated(): void
    {
        $context = new ContaoContext(
            $this->mockContaoFramework(),
            new RequestStack(),
            'staticPlugins'
        );

        $this->assertInstanceOf('Contao\CoreBundle\Asset\ContaoContext', $context);
    }

    public function testReturnsEmptyBasePathInDebugMode()
    {
        $context = new ContaoContext(
            $this->mockContaoFramework(),
            new RequestStack(),
            'staticPlugins',
            true
        );

        $this->assertSame('', $context->getBasePath());
    }

    public function testReturnsEmptyBasePathWithoutRequest()
    {
        $context = new ContaoContext(
            $this->mockContaoFramework(),
            new RequestStack(),
            'staticPlugins'
        );

        $this->assertSame('', $context->getBasePath());
    }

    public function testHandlesEmptyBasePathFromPage()
    {
        $page = $this->mockPageWithDetails();

        $context = new ContaoContext(
            $this->mockContaoFramework(),
            new RequestStack(),
            'staticPlugins'
        );

        $GLOBALS['objPage'] = $page;

        $this->assertSame('', $context->getBasePath());
    }

    /**
     * @dataProvider basePathProvider
     */
    public function testReadsFromPage($domain, $useSSL, $basePath, $expected)
    {
        $request = $this->createMock(Request::class);
        $requestStack = new RequestStack();
        $page = $this->mockPageWithDetails();

        $requestStack->push($request);

        $context = new ContaoContext(
            $this->mockContaoFramework(),
            $requestStack,
            'staticPlugins'
        );

        $request
            ->expects($this->once())
            ->method('getBasePath')
            ->willReturn($basePath)
        ;

        $page->rootUseSSL = $useSSL;
        $page->staticPlugins = $domain;

        $GLOBALS['objPage'] = $page;

        $this->assertSame($expected, $context->getBasePath());
    }

    /**
     * @dataProvider basePathProvider
     */
    public function testReadsFromConfigIfNoPageIsAvailable($domain, $useSSL, $basePath, $expected)
    {
        $config = $this->mockConfiguredAdapter(['get' => $domain]);
        $framework = $this->mockContaoFramework();
        $request = $this->createMock(Request::class);
        $requestStack = new RequestStack();

        $requestStack->push($request);

        $context = new ContaoContext(
            $framework,
            $requestStack,
            'staticPlugins'
        );

        $framework
            ->expects($this->once())
            ->method('createInstance')
            ->with('Contao\Config')
            ->willReturn($config)
        ;

        $request
            ->expects($this->once())
            ->method('getBasePath')
            ->willReturn($basePath)
        ;

        $request
            ->expects($this->once())
            ->method('isSecure')
            ->willReturn($useSSL)
        ;

        $this->assertSame($expected, $context->getBasePath());
    }

    public function basePathProvider()
    {
        return [
            ['example.com', true, '', 'https://example.com'],
            ['example.com', false, '', 'http://example.com'],
            ['example.com', true, '/foo', 'https://example.com/foo'],
            ['example.com', false, '/foo', 'http://example.com/foo'],
            ['example.ch', false, '/bar', 'http://example.ch/bar'],
        ];
    }

    public function testIsSecureFromPage()
    {
        $page = $this->mockPageWithDetails();

        $context = new ContaoContext(
            $this->mockContaoFramework(),
            new RequestStack(),
            ''
        );

        $GLOBALS['objPage'] = $page;

        $page->rootUseSSL = true;
        $this->assertTrue($context->isSecure());

        $page->rootUseSSL = false;
        $this->assertFalse($context->isSecure());
    }

    public function testIsSecureFromRequest()
    {
        $request = new Request();
        $requestStack = new RequestStack();

        $context = new ContaoContext(
            $this->mockContaoFramework(),
            $requestStack,
            ''
        );

        $this->assertFalse($context->isSecure());

        $requestStack->push($request);

        $request->server->set('HTTPS', 'on');
        $this->assertTrue($context->isSecure());

        $request->server->set('HTTPS', 'off');
        $this->assertFalse($context->isSecure());
    }

    private function mockPageWithDetails()
    {
        // Necessary to load the aliased object
        Registry::getInstance();

        $page = new PageModel();

        $page->type = 'root';
        $page->fallback = true;
        $page->staticPlugins = '';

        $page->loadDetails();

        return $page;
    }
}
