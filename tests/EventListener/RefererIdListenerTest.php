<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\EventListener\RefererIdListener;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests the RefererIdListener class.
 *
 * @author Yanick Witschi <https:/github.com/toflar>
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class RefererIdListenerTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $listener = new RefererIdListener($this->mockTokenManager(), $this->mockScopeMatcher());

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\RefererIdListener', $listener);
    }

    /**
     * Tests that the token is added to the request.
     */
    public function testTokenAddedToRequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['test', false]);

        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_BACKEND);

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RefererIdListener($this->mockTokenManager(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has('_contao_referer_id'));
        $this->assertSame('testValue', $request->attributes->get('_contao_referer_id'));
    }

    /**
     * Tests that the token is not added to a front end request.
     */
    public function testTokenNotAddedToFrontendRequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['test', false]);

        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_FRONTEND);

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RefererIdListener($this->mockTokenManager(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->has('_contao_referer_id'));
    }

    /**
     * Tests that the token is not added to a subrequest.
     */
    public function testTokenNotAddedToSubrequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['test', false]);

        $request = new Request();
        $request->attributes->set('_scope', ContaoCoreBundle::SCOPE_BACKEND);

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $listener = new RefererIdListener($this->mockTokenManager(), $this->mockScopeMatcher());
        $listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->has('_contao_referer_id'));
    }
}
