<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Framework;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ScopeCheckingTrait;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This tests the scope aware trait.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class ScopeCheckingTraitTest extends TestCase
{
    /**
     * Generate test data for testIsScope()
     *
     * @return array
     */
    public function isAnyScopeProvider()
    {
        return [
            'Test backend scope matches in isBackendScope()' => [
                true,
                'isBackendScope',
                $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND)
            ],
            'Test frontend scope does not match in isBackendScope()' => [
                false,
                'isBackendScope',
                $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND)
            ],
            'Test request without scope does not match in isBackendScope()' => [
                false,
                'isBackendScope',
                $this->mockRequest()
            ],

            'Test frontend scope matches in isFrontendScope()' => [
                true,
                'isFrontendScope',
                $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND)
            ],
            'Test backend scope does not match in isFrontendScope()' => [
                false,
                'isFrontendScope',
                $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND)
            ],
            'Test request without scope does not match in isFrontendScope()' => [
                false,
                'isBackendScope',
                $this->mockRequest()
            ],
        ];
    }

    /**
     * Test the isScope method.
     *
     * @param bool               $expected  The expected result.
     * @param string             $method    The method to call.
     * @param Request            $request   The request.
     *
     * @dataProvider isAnyScopeProvider
     */
    public function testIsAnyScope($expected, $method, $request)
    {
        $this->assertSame($expected, $this->applyToMock($method, $request));
    }

    /**
     * Generate test data for testIsScope()
     *
     * @return array
     */
    public function isContaoScopeProvider()
    {
        return [
            'Test backend scope matches' => [
                true,
                $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND)
            ],
            'Test frontend scope matches' => [
                true,
                $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND)
            ],
            'Test request without scope does not match' => [
                false,
                $this->mockRequest()
            ],
        ];
    }

    /**
     * Test the isContaoScope() method.
     *
     * @param bool                    $expected  The expected result.
     * @param Request                 $request   The request.
     *
     * @dataProvider isContaoScopeProvider
     */
    public function testIsContaoScope($expected, $request)
    {
        $this->assertSame($expected, $this->applyToMock('isContaoScope', $request));
    }


    /**
     * Generate test data for testIsAnyMasterRequest()
     *
     * @return array
     */
    public function isAnyMasterRequestProvider()
    {
        return [
            'Test backend master request matches in isContaoMasterRequest()' => [
                true,
                'isContaoMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend master request matches in isContaoMasterRequest()' => [
                true,
                'isContaoMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test master request without scope does not match in isContaoMasterRequest()' => [
                false,
                'isContaoMasterRequest',
                $this->getEvent(true, $this->mockRequest())
            ],
            'Test backend sub request does not match in isContaoMasterRequest()' => [
                false,
                'isContaoMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend sub request does not match in isContaoMasterRequest()' => [
                false,
                'isContaoMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test sub request without scope does not match in isContaoMasterRequest()' => [
                false,
                'isContaoMasterRequest',
                $this->getEvent(false, $this->mockRequest())
            ],

            'Test backend master request matches in isBackendMasterRequest()' => [
                true,
                'isBackendMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend master request does not match in isBackendMasterRequest()' => [
                false,
                'isBackendMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test master request without scope does not match in isBackendMasterRequest()' => [
                false,
                'isBackendMasterRequest',
                $this->getEvent(true, $this->mockRequest())
            ],
            'Test backend sub request does not match in isBackendMasterRequest()' => [
                false,
                'isBackendMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend sub request does not match in isBackendMasterRequest()' => [
                false,
                'isBackendMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test sub request without scope does not match in isBackendMasterRequest()' => [
                false,
                'isBackendMasterRequest',
                $this->getEvent(false, $this->mockRequest())
            ],

            'Test backend master request does not match in isFrontendMasterRequest()' => [
                false,
                'isFrontendMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend master request does match in isFrontendMasterRequest()' => [
                true,
                'isFrontendMasterRequest',
                $this->getEvent(true, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test master request without scope does not match in isFrontendMasterRequest()' => [
                false,
                'isFrontendMasterRequest',
                $this->getEvent(true, $this->mockRequest())
            ],
            'Test backend sub request does not match in isFrontendMasterRequest()' => [
                false,
                'isFrontendMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend sub request does not match in isFrontendMasterRequest()' => [
                false,
                'isFrontendMasterRequest',
                $this->getEvent(false, $this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test sub request without scope does not match in isFrontendMasterRequest()' => [
                false,
                'isFrontendMasterRequest',
                $this->getEvent(false, $this->mockRequest())
            ],
        ];
    }

    /**
     * Test the isContaoMasterRequest() method.
     *
     * @param bool        $expected The expected result.
     * @param string      $method   The method to invoke.
     * @param KernelEvent $event    The kernel event.
     *
     * @dataProvider isAnyMasterRequestProvider
     */
    public function testIsAnyMasterRequest($expected, $method, KernelEvent $event)
    {
        $this->assertSame($expected, $this->applyToMock($method, $event));
    }

    /**
     * Apply a method call to a mock.
     *
     * @param string             $method    The method.
     * @param mixed              $argument  The argument to delegate.
     *
     * @return mixed
     */
    private function applyToMock($method, $argument)
    {
        $mock = $this->getMockForTrait(ScopeCheckingTrait::class);

        return \Closure::bind(function ($argument = null) use ($method) {
            return $this->$method($argument);
        }, $mock, $mock)->__invoke($argument);
    }

    private function getEvent($master, $request)
    {
        return new KernelEvent(
            $this->getMockForAbstractClass(HttpKernelInterface::class),
            $request,
            $master ? HttpKernelInterface::MASTER_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }
}
