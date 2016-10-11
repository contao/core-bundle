<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel\HttpCache;

use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the PreflightRequestHttpCache class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PreflightRequestHttpCacheTest extends TestCase
{
    public function testInstantiation()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $this->assertInstanceOf('Contao\CoreBundle\HttpKernel\HttpCache\PreflightRequestHttpCache', $preflightKernel);
    }

    public function testNoPreflightIsExecutedWhenRequestDoesNotMatch()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $preflightKernel->setMockResponse(new Response());

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');

        $preflightKernel->handle($request);
        $this->assertTrue($preflightKernel->getParentHandleWasCalled());
        $this->assertFalse($preflightKernel->getParentPassWasCalled());
    }

    public function testPreflightIsExecutedWhenRequestDoesMatch()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $preflightKernel->setMockResponse(new Response());
        $preflightKernel->setMockPreflightResponse(new Response());

        $request = new Request();
        $request->headers->set('Authorization', 'foobar');

        $preflightKernel->handle($request);
        $this->assertTrue($preflightKernel->getParentHandleWasCalled());
        $this->assertTrue($preflightKernel->getParentPassWasCalled());
    }

    public function testPreflightIsExecutedWhenRequestDoesMatchOnCookies()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $preflightKernel->setMockResponse(new Response());
        $preflightKernel->setMockPreflightResponse(new Response());

        $request = new Request();
        $request->cookies->set('My-Cookie', 'foobar');

        $preflightKernel->handle($request);
        $this->assertTrue($preflightKernel->getParentHandleWasCalled());
        $this->assertTrue($preflightKernel->getParentPassWasCalled());
    }

    public function testPreflightRequestIsCorrectlyBuilt()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $options = $preflightKernel->getPreflightOptions();

        $request = Request::create(
            'https://www.domain.tld/about/us?foo=bar',
            'GET',
            [],
            [],
            [],
            ['HTTP_VERY_IMPORTANT_HEADER' => 'actually;not']
        );


        $preflightRequest = $preflightKernel->createPreflightRequest($request);

        $this->assertSame($options['preflightPath'], $preflightRequest->getPathInfo());
        $this->assertTrue($preflightRequest->headers->has('Very-Important-Header'));
        $this->assertTrue($preflightRequest->headers->has($options['originalPathHeaderName']));
        $this->assertSame('/about/us?foo=bar', $preflightRequest->headers->get($options['originalPathHeaderName']));
    }

    public function testRequestIsCorrectlyDecoratedByPreflightRequestHeaders()
    {
        $preflightKernel = new PreflightRequestHttpCache($this->mockKernel(), __DIR__);
        $options = $preflightKernel->getPreflightOptions();

        $request = new Request();
        $preflightResponse = new Response();


        $preflightResponse->headers->set($options['decorateHeadersPrefix'] . 'Foobar', 'foobar');
        $preflightResponse->headers->set('Some-Irrelevant-Header', 'foobar');

        $preflightKernel->decorateRequestWithPreflightResponse($request, $preflightResponse);

        $this->assertTrue($request->headers->has($options['decorateHeadersPrefix'] . 'Foobar'));
        $this->assertFalse($request->headers->has('Some-Irrelevant-Header'));
    }
}
