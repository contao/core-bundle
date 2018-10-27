<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Picker;

use Contao\CoreBundle\Picker\PagePickerProvider;
use Contao\CoreBundle\Picker\Picker;
use Contao\CoreBundle\Picker\PickerConfig;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PickerTest extends TestCase
{
    /**
     * @var Picker
     */
    private $picker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('Page picker')
        ;

        $factory = new MenuFactory();
        $router = $this->createMock(RouterInterface::class);
        $provider = new PagePickerProvider($factory, $router, $translator);
        $config = new PickerConfig('page', [], 5, 'pagePicker');

        $this->picker = new Picker($factory, [$provider], $config);
    }

    public function testReturnsTheConfiguration(): void
    {
        $this->assertSame('page', $this->picker->getConfig()->getContext());
    }

    public function testReturnsTheMenu(): void
    {
        $menu = $this->picker->getMenu();

        $this->assertSame('picker', $menu->getName());
        $this->assertSame(1, $menu->count());

        $pagePicker = $menu->getChild('pagePicker');

        $this->assertNotNull($pagePicker);
        $this->assertTrue($pagePicker->isCurrent());
        $this->assertSame('Page picker', $pagePicker->getLabel());

        $this->assertSame($menu, $this->picker->getMenu());
    }

    public function testReturnsTheCurrentProvider(): void
    {
        $provider = $this->picker->getCurrentProvider();

        $this->assertNotNull($provider);
        $this->assertSame('pagePicker', $provider->getName());
    }

    public function testReturnsNullIfThereIsNoCurrentProvider(): void
    {
        $factory = new MenuFactory();
        $router = $this->createMock(RouterInterface::class);
        $provider = new PagePickerProvider($factory, $router);
        $config = new PickerConfig('page');
        $picker = new Picker($factory, [$provider], $config);

        $this->assertNull($picker->getCurrentProvider());
    }

    public function testReturnsTheCurrentUrl(): void
    {
        $this->assertNull($this->picker->getCurrentUrl());
    }

    public function testReturnsNullAsCurrentUrlIfThereIsNoCurrentMenuItem(): void
    {
        $factory = new MenuFactory();
        $router = $this->createMock(RouterInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $provider = new PagePickerProvider($factory, $router, $translator);
        $config = new PickerConfig('page');
        $picker = new Picker($factory, [$provider], $config);

        $this->assertNull($picker->getCurrentUrl());
    }

    public function testFailsToReturnTheCurrentUrlIfThereAreNoMenuItems(): void
    {
        $factory = new MenuFactory();
        $config = new PickerConfig('page', [], 5, 'pagePicker');
        $picker = new Picker($factory, [], $config);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No picker menu items found');

        $picker->getCurrentUrl();
    }
}
