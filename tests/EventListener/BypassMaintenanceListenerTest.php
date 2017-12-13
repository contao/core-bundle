<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\EventListener\BypassMaintenanceListener;
use Contao\CoreBundle\Security\TokenChecker;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class BypassMaintenanceListenerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $session = $this->mockSession();
        $listener = new BypassMaintenanceListener($session, new TokenChecker($session));

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\BypassMaintenanceListener', $listener);
    }

    public function testAddsTheRequestAttribute(): void
    {
        $request = new Request();

        $kernel = $this->createMock(KernelInterface::class);
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $tokenChecker = $this->createMock(TokenChecker::class);
        $tokenChecker
            ->expects($this->once())
            ->method('isAuthenticated')
            ->with(BackendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $listener = new BypassMaintenanceListener($this->mockSession(), $tokenChecker);
        $listener->onKernelRequest($event);

        $this->assertTrue($event->getRequest()->attributes->get('_bypass_maintenance'));
    }

    public function testDoesNotAddTheRequestAttributeIfThereIsNoBackEndUser(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $request = new Request();
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $tokenChecker = $this->createMock(TokenChecker::class);
        $tokenChecker
            ->expects($this->once())
            ->method('isAuthenticated')
            ->with(BackendUser::SECURITY_SESSION_KEY)
            ->willReturn(false)
        ;

        $listener = new BypassMaintenanceListener($this->mockSession(), $tokenChecker);
        $listener->onKernelRequest($event);

        $this->assertFalse($event->getRequest()->attributes->has('_bypass_maintenance'));
    }
}
