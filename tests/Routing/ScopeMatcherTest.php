<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Tests the ScopeMatcher class.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ScopeMatcherTest extends TestCase
{
    /**
     * @var ScopeMatcher
     */
    private $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->matcher = $this->mockScopeMatcher();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Routing\ScopeMatcher', $this->matcher);
    }

    /**
     * Tests the request methods.
     *
     * @dataProvider masterRequestProvider
     */
    public function testRequestMethods($scope, $requestType, $isMaster, $isFrontend, $isBackend)
    {
        $request = new Request();
        $request->attributes->set('_scope', $scope);

        $event = new KernelEvent($this->getMock(KernelInterface::class), $request, $requestType);

        $this->assertEquals($isMaster, $this->matcher->isContaoMasterRequest($event));
        $this->assertEquals($isMaster && $isFrontend, $this->matcher->isFrontendMasterRequest($event));
        $this->assertEquals($isMaster && $isBackend, $this->matcher->isBackendMasterRequest($event));
        $this->assertEquals($isFrontend, $this->matcher->isFrontendRequest($request));
        $this->assertEquals($isBackend, $this->matcher->isBackendRequest($request));
    }

    /**
     * Provides the data for the request tests.
     *
     * @return array   
     */
    public function masterRequestProvider()
    {
        return [
            [
                ContaoCoreBundle::SCOPE_BACKEND,
                HttpKernelInterface::MASTER_REQUEST,
                true,
                false,
                true,
            ],
            [
                ContaoCoreBundle::SCOPE_FRONTEND,
                HttpKernelInterface::MASTER_REQUEST,
                true,
                true,
                false,
            ],
            [
                null,
                HttpKernelInterface::MASTER_REQUEST,
                false,
                false,
                false,
            ],
            [
                ContaoCoreBundle::SCOPE_BACKEND,
                HttpKernelInterface::SUB_REQUEST,
                false,
                false,
                true,
            ],
            [
                ContaoCoreBundle::SCOPE_FRONTEND,
                HttpKernelInterface::SUB_REQUEST,
                false,
                true,
                false,
            ],
            [
                null,
                HttpKernelInterface::SUB_REQUEST,
                false,
                false,
                false,
            ],
        ];
    }
}
