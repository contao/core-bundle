<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Twig\Runtime;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Fragment\Reference\ContentElementReference;
use Contao\CoreBundle\Tests\TestCase;
use Contao\CoreBundle\Twig\Runtime\FragmentRuntime;
use Contao\ModuleModel;

class FragmentRuntimeTest extends TestCase
{
    public function testRenderModuleFromType(): void
    {
        $controllerAdapter = $this->mockAdapter(['getFrontendModule']);
        $controllerAdapter
            ->expects($this->once())
            ->method('getFrontendModule')
            ->with($this->callback(
                function (ModuleModel $model) {
                    $this->assertSame(['type' => 'navigation', 'foo' => 'bar'], $model->row());

                    return true;
                },
            ))
            ->willReturn('runtime-result')
        ;

        $framework = $this->mockContaoFramework(
            [Controller::class => $controllerAdapter],
            [ModuleModel::class => $this->mockClassWithProperties(ModuleModel::class)],
        );

        $runtime = new FragmentRuntime($framework);
        $result = $runtime->renderModule('navigation', ['foo' => 'bar']);

        $this->assertSame('runtime-result', $result);
    }

    public function testRenderModuleFromId(): void
    {
        $controllerAdapter = $this->mockAdapter(['getFrontendModule']);
        $controllerAdapter
            ->expects($this->once())
            ->method('getFrontendModule')
            ->with($this->callback(
                function (ModuleModel $model) {
                    $this->assertSame(['id' => 42, 'type' => 'navigation', 'foo' => 'bar'], $model->row());

                    return true;
                },
            ))
            ->willReturn('runtime-result')
        ;

        $moduleAdapter = $this->mockAdapter(['findById']);
        $moduleAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($this->mockClassWithProperties(ModuleModel::class, ['id' => 42, 'type' => 'navigation']))
        ;

        $framework = $this->mockContaoFramework([
            Controller::class => $controllerAdapter,
            ModuleModel::class => $moduleAdapter,
        ]);

        $runtime = new FragmentRuntime($framework);
        $result = $runtime->renderModule(42, ['foo' => 'bar']);

        $this->assertSame('runtime-result', $result);
    }

    public function testRenderContentFromType(): void
    {
        $controllerAdapter = $this->mockAdapter(['getContentElement']);
        $controllerAdapter
            ->expects($this->once())
            ->method('getContentElement')
            ->with($this->callback(
                function (ContentModel $model) {
                    $this->assertSame(['type' => 'text', 'foo' => 'bar', 'headline' => serialize(['unit' => 'h2', 'value' => 'Test'])], $model->row());

                    return true;
                },
            ))
            ->willReturn('runtime-result')
        ;

        $framework = $this->mockContaoFramework(
            [Controller::class => $controllerAdapter],
            [ContentModel::class => $this->mockClassWithProperties(ContentModel::class)],
        );

        $runtime = new FragmentRuntime($framework);
        $result = $runtime->renderContent('text', ['foo' => 'bar', 'headline' => ['unit' => 'h2', 'value' => 'Test']]);

        $this->assertSame('runtime-result', $result);
    }

    public function testRenderNestedContent(): void
    {
        $controllerAdapter = $this->mockAdapter(['getContentElement']);
        $controllerAdapter
            ->expects($this->once())
            ->method('getContentElement')
            ->with($this->callback(
                function (ContentElementReference $reference) {
                    $this->assertSame(
                        ['type' => 'slider', 'headline' => serialize(['unit' => 'h2', 'value' => 'Test'])],
                        $reference->getContentModel()->row(),
                    );
                    $this->assertSame(
                        ['type' => 'text', 'text' => '<p>Test</p>'],
                        $reference->attributes['nestedFragments'][0]->getContentModel()->row(),
                    );

                    return true;
                },
            ))
            ->willReturn('runtime-result')
        ;

        $framework = $this->mockContaoFramework(
            [Controller::class => $controllerAdapter],
            [ContentModel::class => fn () => $this->mockClassWithProperties(ContentModel::class)],
        );

        $runtime = new FragmentRuntime($framework);

        $result = $runtime->renderContent('slider', [
            'headline' => [
                'unit' => 'h2',
                'value' => 'Test',
            ],
            'nested_fragments' => [
                [
                    'type' => 'text',
                    'text' => '<p>Test</p>',
                ],
            ],
        ]);

        $this->assertSame('runtime-result', $result);
    }

    public function testRenderContentFromId(): void
    {
        $controllerAdapter = $this->mockAdapter(['getContentElement']);
        $controllerAdapter
            ->expects($this->once())
            ->method('getContentElement')
            ->with($this->callback(
                function (ContentModel $model) {
                    $this->assertSame(['id' => 42, 'type' => 'text', 'foo' => 'bar'], $model->row());

                    return true;
                },
            ))
            ->willReturn('runtime-result')
        ;

        $contentAdapter = $this->mockAdapter(['findById']);
        $contentAdapter
            ->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($this->mockClassWithProperties(ContentModel::class, ['id' => 42, 'type' => 'text']))
        ;

        $framework = $this->mockContaoFramework([
            Controller::class => $controllerAdapter,
            ContentModel::class => $contentAdapter,
        ]);

        $runtime = new FragmentRuntime($framework);
        $result = $runtime->renderContent(42, ['foo' => 'bar']);

        $this->assertSame('runtime-result', $result);
    }
}
