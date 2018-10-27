<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Controller;

use Contao\CoreBundle\Controller\InsertTagsController;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

class InsertTagsControllerTest extends TestCase
{
    public function testRendersNonCacheableInsertTag(): void
    {
        $framework = $this->mockContaoFramework();
        $framework
            ->method('initialize')
        ;

        $framework
            ->method('createInstance')
            ->willReturn($this->mockConfiguredAdapter(['replace' => '3858f62230ac3c915f300c664312c63f']))
        ;

        $controller = new InsertTagsController($framework);
        $response = $controller->renderAction(new Request(), '{{request_token}}');

        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertNull($response->getMaxAge());
        $this->assertSame('3858f62230ac3c915f300c664312c63f', $response->getContent());

        $request = new Request();
        $request->query->set('clientCache', 300);

        $controller = new InsertTagsController($framework);
        $response = $controller->renderAction($request, '{{request_token}}');

        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertSame(300, $response->getMaxAge());
        $this->assertSame('3858f62230ac3c915f300c664312c63f', $response->getContent());
    }
}
