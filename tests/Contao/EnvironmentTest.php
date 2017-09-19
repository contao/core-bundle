<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\CoreBundle\Tests\TestCase;
use Contao\Environment;
use Contao\System;

/**
 * Tests the Environment class.
 *
 * @group contao3
 */
class EnvironmentTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        Environment::reset();
        Environment::set('path', '/core');

        require __DIR__.'/../../src/Resources/contao/config/default.php';
        require __DIR__.'/../../src/Resources/contao/config/agents.php';
    }

    /**
     * Tests the mod_php environment.
     */
    public function testHandlesModPhp(): void
    {
        $this->setSapi('apache');

        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_CONNECTION'] = 'keep-alive';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-GB;q=0.6,en;q=0.4';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.789.0';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['DOCUMENT_ROOT'] = $this->getRootDir();
        $_SERVER['SCRIPT_FILENAME'] = $this->getRootDir().'/core/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['QUERY_STRING'] = 'do=test';
        $_SERVER['REQUEST_URI'] = '/core/en/academy.html?do=test';
        $_SERVER['SCRIPT_NAME'] = '/core/index.php';
        $_SERVER['PHP_SELF'] = '/core/index.php';

        $this->runTests();
    }

    /**
     * Tests the cgi_fcgi environment.
     */
    public function testHandlesCgiFcgi(): void
    {
        $this->setSapi('cgi_fcgi');

        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_CONNECTION'] = 'close';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-GB;q=0.6,en;q=0.4';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.789.0';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['DOCUMENT_ROOT'] = $this->getRootDir();
        $_SERVER['SCRIPT_FILENAME'] = $this->getRootDir().'/core/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['QUERY_STRING'] = 'do=test';
        $_SERVER['REQUEST_URI'] = '/core/en/academy.html?do=test';
        $_SERVER['SCRIPT_NAME'] = '/core/index.php';
        $_SERVER['PHP_SELF'] = '/core/index.php';
        $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
        $_SERVER['PATH_INFO'] = '/en/academy.html';
        $_SERVER['SCRIPT_URI'] = 'http://localhost/core/en/academy.html';
        $_SERVER['SCRIPT_URL'] = '/core/en/academy.html';

        $this->runTests();
    }

    /**
     * Tests the fpm_fcgi environment.
     */
    public function testHandlesFpmFcgi(): void
    {
        $this->setSapi('fpm_fcgi');

        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_CONNECTION'] = 'close';
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.8,en-GB;q=0.6,en;q=0.4';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '123.456.789.0';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['DOCUMENT_ROOT'] = $this->getRootDir();
        $_SERVER['SCRIPT_FILENAME'] = $this->getRootDir().'/core/index.php';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['QUERY_STRING'] = 'do=test';
        $_SERVER['REQUEST_URI'] = 'http://localhost/core/en/academy.html?do=test'; // see #8661
        $_SERVER['SCRIPT_NAME'] = '/core/index.php';
        $_SERVER['PHP_SELF'] = '/core/index.php';
        $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
        $_SERVER['PATH_INFO'] = '/en/academy.html';

        $this->runTests();
    }

    /**
     * Returns the normalized root directory.
     *
     * @return string
     */
    public function getRootDir(): string
    {
        return strtr(parent::getRootDir(), '\\', '/');
    }

    /**
     * Runs the actual tests.
     */
    protected function runTests(): void
    {
        $container = $this->mockContainerWithContaoScopes();
        $request = $container->get('request_stack')->getCurrentRequest();

        $request->server->set('REMOTE_ADDR', '123.456.789.0');
        $request->server->set('SCRIPT_NAME', '/core/index.php');
        $request->server->set('HTTPS', 'on');

        System::setContainer($container);

        $agent = Environment::get('agent');

        $this->assertSame('mac', $agent->os);
        $this->assertSame('mac chrome blink ch33', $agent->class);
        $this->assertSame('chrome', $agent->browser);
        $this->assertSame('ch', $agent->shorty);
        $this->assertSame('33', $agent->version);
        $this->assertSame('blink', $agent->engine);
        $this->assertSame(['33', '0', '1750', '149'], $agent->versions);
        $this->assertFalse($agent->mobile);

        $this->assertSame('HTTP/1.1', Environment::get('serverProtocol'));
        $this->assertSame($this->getRootDir().'/core/index.php', Environment::get('scriptFilename'));
        $this->assertSame('/core/index.php', Environment::get('scriptName'));
        $this->assertSame($this->getRootDir(), Environment::get('documentRoot'));
        $this->assertSame('/core/en/academy.html?do=test', Environment::get('requestUri'));
        $this->assertSame(['de-DE', 'de', 'en-GB', 'en'], Environment::get('httpAcceptLanguage'));
        $this->assertSame(['gzip', 'deflate', 'sdch'], Environment::get('httpAcceptEncoding'));
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.149 Safari/537.36', Environment::get('httpUserAgent'));
        $this->assertSame('localhost', Environment::get('httpHost'));
        $this->assertEmpty(Environment::get('httpXForwardedHost'));

        $this->assertTrue(Environment::get('ssl'));
        $this->assertSame('https://localhost', Environment::get('url'));
        $this->assertSame('https://localhost/core/en/academy.html?do=test', Environment::get('uri'));
        $this->assertSame('123.456.789.0', Environment::get('ip'));
        $this->assertSame('127.0.0.1', Environment::get('server'));
        $this->assertSame('index.php', Environment::get('script'));
        $this->assertSame('en/academy.html?do=test', Environment::get('request'));
        $this->assertSame('en/academy.html?do=test', Environment::get('indexFreeRequest'));
        $this->assertSame('https://localhost'.Environment::get('path').'/', Environment::get('base'));
        $this->assertFalse(Environment::get('isAjaxRequest'));
    }

    /**
     * Overrides the SAPI value.
     *
     * @param string $sapi
     */
    private function setSapi(string $sapi): void
    {
        $reflection = new \ReflectionClass(Environment::class);

        $property = $reflection->getProperty('strSapi');
        $property->setAccessible(true);
        $property->setValue($sapi);
    }
}
