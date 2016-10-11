<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel\HttpCache;

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This class executes a preflight request to the application if the request
 * contains certain (configurable) HTTP headers. This preflight request contains
 * the full original request (except for the path obviously) which is passed on
 * as an HTTP header for reference.
 * The original request is then decorated by the headers of the response of the
 * preflight request and finally passed on to the regular Symfony HTTP cache.
 *
 * This mainly allows to implement advanced caching. If your application contains
 * state, a lot of the information might be bound to the cookie which is sent by
 * a single HTTP header (named "Cookie"). For any proxy it is thus impossible to
 * represent different cache entries for the same URI as it can only vary (HTTP
 * Vary header) on the Cookie header which is not recommended as it would generate
 * way too many cache entries. The preflight request allows you to sort of split
 * e.g. the sent Cookie header into different headers which will then allow you
 * to easily vary on.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PreflightRequestHttpCache extends HttpCache
{
    private $mockResponse = null;
    private $mockPreflightResponse = null;
    private $parentHandleWasCalled = false;
    private $parentPassWasCalled = false;

    private $preflightOptions = [
        'matchHeaders'           => ['Cookie', 'Authorization'],
        'preflightPath'          => '/contao/preflight',
        'decorateHeadersPrefix'  => 'Contao-Vary-',
        'originalPathHeaderName' => 'Contao-Original-Path'
    ];

    /**
     * @return array
     */
    public function getPreflightOptions()
    {
        return $this->preflightOptions;
    }

    /**
     * @param array $preflightOptions
     */
    public function setPreflightOptions(array $preflightOptions)
    {
        $this->preflightOptions = $preflightOptions;
    }

    /**
     * Mock the response (useful for unit testing).
     *
     * @param Response $response
     */
    public function setMockResponse(Response $response = null)
    {
        $this->mockResponse = $response;
    }

    /**
     * Mock the preflight response (useful for unit testing).
     *
     * @param Response $response
     */
    public function setMockPreflightResponse(Response $response = null)
    {
        $this->mockPreflightResponse = $response;
    }

    /**
     * Only needed for unit testing.
     *
     * @return boolean
     */
    public function getParentHandleWasCalled()
    {
        return $this->parentHandleWasCalled;
    }

    /**
     * Only needed for unit testing.
     *
     * @return boolean
     */
    public function getParentPassWasCalled()
    {
        return $this->parentPassWasCalled;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (!$this->matchesPreflightRequirements($request)) {

            return $this->parentHandle($request, $type, $catch);
        }

        $preflightRequest = $this->createPreflightRequest($request);

        $preflightResponse = $this->parentPass($preflightRequest, $catch);

        $this->decorateRequestWithPreflightResponse($request, $preflightResponse);

        return $this->parentHandle($request, $type, $catch);
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return null|Response
     */
    private function parentHandle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->parentHandleWasCalled = true;

        if (null !== $this->mockResponse) {

            return $this->mockResponse;
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * @param Request $request
     * @param         $catch
     *
     * @return null|Response
     */
    private function parentPass(Request $request, $catch)
    {
        $this->parentPassWasCalled = true;

        if (null !== $this->mockPreflightResponse) {

            return $this->mockPreflightResponse;
        }

        return parent::pass($request, $catch);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function matchesPreflightRequirements(Request $request)
    {
        // A none safe request is always ignored
        if (!$request->isMethodSafe()) {

            return false;
        }

        foreach ($this->preflightOptions['matchHeaders'] as $header) {
            if ('Cookie' === $header) {
                if (0 !== $request->cookies->count()) {

                    return true;
                }
            }

            if ($request->headers->has($header)) {

                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return Request
     */
    public function createPreflightRequest(Request $request)
    {
        $queryString = $request->getQueryString();

        // Override path
        $server = $request->server->all();
        $server['REQUEST_URI'] = $this->preflightOptions['preflightPath'] . ('' !== $queryString ? '?' . $queryString : '');

        $preflightRequest = $request->duplicate(null, null, null, null, null, $server);
        $preflightRequest->headers->set(
            $this->preflightOptions['originalPathHeaderName'],
            $request->getPathInfo() . ('' !== $queryString ? '?' . $queryString : '')
        );

        return $preflightRequest;
    }

    /**
     * @param Request  $request
     * @param Response $preflightResponse
     */
    public function decorateRequestWithPreflightResponse(Request $request, Response $preflightResponse)
    {
        $rgxp = '/^' . preg_quote($this->preflightOptions['decorateHeadersPrefix'], '/') . '/i';

        foreach ($preflightResponse->headers->all() as $header => $value) {
            if (preg_match($rgxp, $header)) {
                $request->headers->set($header, $value);
            }
        }
    }
}
