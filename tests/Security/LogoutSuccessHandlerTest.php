<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security;

use Contao\CoreBundle\Security\LogoutSuccessHandler;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Tests the LogoutSuccessHandler class.
 */
class LogoutSuccessHandlerTest extends TestCase
{
    protected $router;
    protected $session;
    protected $request;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->request = new Request();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $handler = new LogoutSuccessHandler($this->router);

        $this->assertInstanceOf('Contao\CoreBundle\Security\LogoutSuccessHandler', $handler);
    }

    /**
     * Tests the handler if no logout target is given.
     */
    public function testRedirectWithoutLogoutTarget(): void
    {
        $this->session
            ->expects($this->once())
            ->method('has')
            ->willReturn(false)
        ;

        $this->request->setSession($this->session);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('contao_root')
            ->willReturn('/')
        ;

        $handler = new LogoutSuccessHandler($this->router);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $handler->onLogoutSuccess($this->request));
    }

    /**
     * Tests the handler if a logout target is given.
     */
    public function testRedirectWithLogoutTarget(): void
    {
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('_contao_logout_target')
            ->willReturn(true)
        ;

        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('_contao_logout_target')
            ->willReturn('/')
        ;

        $this->request->setSession($this->session);

        $handler = new LogoutSuccessHandler($this->router);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $handler->onLogoutSuccess($this->request));
    }
}
