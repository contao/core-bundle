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
use Contao\CoreBundle\Framework\ScopeAwareTrait;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This tests the scope aware trait.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class ScopeAwareTraitTest extends TestCase
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
            'Test backend scope matches in isBackendScope() with container fallback' => [
                true,
                'isBackendScope',
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend scope does not match in isBackendScope() with container fallback' => [
                false,
                'isBackendScope',
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test no match in isBackendScope() without request and container' => [
                false,
                'isBackendScope',
                null,
                null
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
            'Test frontend scope matches in isFrontendScope() with container fallback' => [
                true,
                'isFrontendScope',
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test backend scope does not match in isFrontendScope() with container fallback' => [
                false,
                'isFrontendScope',
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test no match in isFrontendScope() without request and container' => [
                false,
                'isFrontendScope',
                null,
                null
            ],
        ];
    }

    /**
     * Test the isScope method.
     *
     * @param bool               $expected  The expected result.
     * @param string             $method    The method to call.
     * @param Request            $request   The request.
     * @param ContainerInterface $container Optional container.
     *
     * @dataProvider isAnyScopeProvider
     */
    public function testIsAnyScope($expected, $method, $request, ContainerInterface $container = null)
    {
        $this->assertSame($expected, $this->applyToMock($method, $request, $container));
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
            'Test backend scope matches with container fallback' => [
                true,
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_BACKEND))
            ],
            'Test frontend scope matches with container fallback' => [
                true,
                null,
                $this->mockContainerWithRequest($this->mockRequest(ContaoCoreBundle::SCOPE_FRONTEND))
            ],
            'Test no match without request and container' => [
                false,
                null,
                null
            ],
        ];
    }

    /**
     * Test the isContaoScope() method.
     *
     * @param bool                    $expected  The expected result.
     * @param Request                 $request   The request.
     * @param ContainerInterface|null $container Optional container.
     *
     * @dataProvider isContaoScopeProvider
     */
    public function testIsContaoScope($expected, $request, ContainerInterface $container = null)
    {
        $this->assertSame($expected, $this->applyToMock('isContaoScope', $request, $container));
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
     * @param ContainerInterface $container The optional container to use.
     *
     * @return mixed
     */
    private function applyToMock($method, $argument, ContainerInterface $container = null)
    {
        $mock = $this->getTrait($container);

        return \Closure::bind(function ($argument = null) use ($method) {
            return $this->$method($argument);
        }, $mock, $mock)->__invoke($argument);
    }

    /**
     * Create a trait optionally using a container.
     *
     * @param ContainerInterface|null $container The optional container.
     *
     * @return ScopeAwareTrait
     */
    private function getTrait(ContainerInterface $container = null)
    {
        $mock = $this->getMockForTrait(ScopeAwareTrait::class);

        if (null !== $container) {
            $mock->setContainer($container);
        }

        return $mock;
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
