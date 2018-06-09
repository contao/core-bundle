<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Tests\Fixtures\Controller\ContentElement\TestController;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendTemplate;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class ContentElementControllerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $controller = new TestController();

        $this->assertInstanceOf('Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController', $controller);
        $this->assertInstanceOf('Contao\CoreBundle\Controller\AbstractFragmentController', $controller);
    }

    public function testCreatesTemplateFromClassname(): void
    {
        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $controller(new Request(), new ContentModel(), 'main');
    }

    public function testCreatesTemplateFromFragmentOptionsType(): void
    {
        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_foo'));
        $controller->setFragmentOptions(['type' => 'foo']);

        $controller(new Request(), new ContentModel(), 'main');
    }

    public function testCreatesTemplateFromCustomTpl(): void
    {
        $model = new ContentModel();
        $model->customTpl = 'ce_bar';

        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_bar'));

        $controller(new Request(), $model, 'main');
    }

    public function testSetsClassFromType(): void
    {
        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $response = $controller(new Request(), new ContentModel(), 'main');
        $template = json_decode($response->getContent());

        $this->assertSame('', $template->cssID);
        $this->assertSame('ce_test', $template->class);
    }

    public function testSetsHeadlineFromModel(): void
    {
        $model = new ContentModel();
        $model->headline = serialize(['unit' => 'h6', 'value' => 'foobar']);

        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $response = $controller(new Request(), $model, 'main');
        $template = json_decode($response->getContent());

        $this->assertSame('foobar', $template->headline);
        $this->assertSame('h6', $template->hl);
    }

    public function testSetsCssIDAndClassFromModel(): void
    {
        $model = new ContentModel();
        $model->cssID = serialize(['foo', 'bar']);

        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $response = $controller(new Request(), $model, 'main');
        $template = json_decode($response->getContent());

        $this->assertSame(' id="foo"', $template->cssID);
        $this->assertSame('ce_test bar', $template->class);
    }

    public function testSetsSection(): void
    {
        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $response = $controller(new Request(), new ContentModel(), 'left');
        $template = json_decode($response->getContent());

        $this->assertSame('left', $template->inColumn);
    }

    public function testSetsClasses(): void
    {
        $controller = new TestController();
        $controller->setContainer($this->mockContainerWithFrameworkTemplate('ce_test'));

        $response = $controller(new Request(), new ContentModel(), 'main', ['first', 'last']);
        $template = json_decode($response->getContent());

        $this->assertSame('ce_test first last', $template->class);
    }

    private function mockContainerWithFrameworkTemplate(string $templateName)
    {
        $framework = $this->mockContaoFramework();
        $framework
            ->expects($this->once())
            ->method('createInstance')
            ->with(FrontendTemplate::class, [$templateName])
            ->willReturn(new FrontendTemplate())
        ;

        $container = new ContainerBuilder();
        $container->set('contao.framework', $framework);

        return $container;
    }
}
