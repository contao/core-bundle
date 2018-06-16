<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Security\TwoFactor;

use Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorFormRenderer;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ContaoTwoFactorFormRendererTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $renderer = new ContaoTwoFactorFormRenderer($this->createMock(RouterInterface::class));

        $this->assertInstanceOf('Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorFormRenderer', $renderer);
    }

    public function testRedirectsOnRenderFormCall(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('generate')
            ->with('contao_backend_2fa')
            ->willReturn('/contao/2fa')
        ;

        $renderer = new ContaoTwoFactorFormRenderer($router);
        $response = $renderer->renderForm($this->createMock(Request::class), []);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }
}
