<?php

namespace Contao\CoreBundle\Test\Routing;

use Contao\BackendRoute;
use Contao\CoreBundle\Test\TestCase;
use Contao\CoreBundle\Twig\Extension\ContaoBaseTemplateExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ContaoBaseTemplateExtensionTest extends TestCase
{
    public function testContaoBaseTemplate()
    {
        $backendRoute = $this->getMockBuilder(BackendRoute::class)
            ->setMethods(['getBaseTemplate', 'run'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $template = new \stdClass();

        $backendRoute
            ->expects($this->once())
            ->method('getBaseTemplate')
            ->willReturn($template)
        ;

        $backendRoute
            ->expects($this->once())
            ->method('run')
            ->willReturn(new Response())
        ;

        $request = new Request();
        $request->attributes->set('_scope', 'backend');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $contaoFramework = $this->mockContaoFramework(null, null, [], [
            BackendRoute::class => $backendRoute
        ]);

        $extension = new ContaoBaseTemplateExtension($requestStack, $contaoFramework);
        $extension->contaoBaseTemplate([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ]);

        $this->assertSame('a', $template->a);
        $this->assertSame('b', $template->b);
        $this->assertSame('c', $template->c);
    }
}