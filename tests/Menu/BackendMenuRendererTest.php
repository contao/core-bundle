<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Menu\BackendMenu;

use Contao\CoreBundle\Menu\BackendMenuRenderer;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class BackendMenuRendererTest extends TestCase
{
    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templating;

    /**
     * @var BackendMenuRenderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TL_LANG']['MSC']['skipNavigation'] = 'Skip navigation';

        $this->templating = $this->createMock(Environment::class);
        $this->renderer = new BackendMenuRenderer($this->templating);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\BackendMenuRenderer', $this->renderer);
    }

    public function testRendersTheBackendMenuTemplate(): void
    {
        $node = $this->createMock(ItemInterface::class);

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->with(
                'ContaoCoreBundle:Backend:be_menu.html.twig',
                [
                    'tree' => $node,
                    'lang' => [
                        'skipNavigation' => $GLOBALS['TL_LANG']['MSC']['skipNavigation'],
                    ],
                ]
            )
            ->willReturn('<html/>')
        ;

        $this->renderer->render($node);
    }
}
