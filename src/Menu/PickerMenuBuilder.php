<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Contao\CoreBundle\Event\BuildPickerMenuEvent;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Knp\Menu\FactoryInterface;
use Knp\Menu\Renderer\RendererInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PickerMenuBuilder
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param FactoryInterface         $factory
     * @param RendererInterface        $renderer
     */
    public function __construct(RequestStack $requestStack, EventDispatcherInterface $dispatcher, FactoryInterface $factory, RendererInterface $renderer)
    {
        $this->requestStack = $requestStack;
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->renderer = $renderer;
    }

    /**
     * Creates the file menu.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function createMenu()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('No request object given');
        }

        $menu = $this->factory->createItem('picker');

        $this->dispatcher->dispatch(
            ContaoCoreEvents::BUILD_PICKER_MENU,
            new BuildPickerMenuEvent($this->factory, $menu)
        );

        return $this->renderer->render($menu);
    }
}
