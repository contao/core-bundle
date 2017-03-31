<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Contao\BackendUser;
use Contao\CoreBundle\Event\BuildPickerMenuEvent;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

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
     * @var RouterInterface
     */
    private $router;

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
     * @var string
     */
    private $pagePickerLabel = 'Page picker';

    /**
     * @var string
     */
    private $filePickerLabel = 'File picker';

    /**
     * Constructor.
     *
     * @param RequestStack             $requestStack
     * @param RouterInterface          $router
     * @param EventDispatcherInterface $dispatcher
     * @param FactoryInterface         $factory
     * @param RendererInterface        $renderer
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router, EventDispatcherInterface $dispatcher, FactoryInterface $factory, RendererInterface $renderer)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->renderer = $renderer;
    }

    /**
     * Sets the page picker label.
     *
     * @param string $pagePickerLabel
     */
    public function setPagePickerLabel($pagePickerLabel)
    {
        $this->pagePickerLabel = $pagePickerLabel;
    }

    /**
     * Sets the file picker label.
     *
     * @param string $filePickerLabel
     */
    public function setFilePickerLabel($filePickerLabel)
    {
        $this->filePickerLabel = $filePickerLabel;
    }

    /**
     * Creates the file menu.
     *
     * @param BackendUser $user
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function createMenu(BackendUser $user)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new \RuntimeException('No request object given');
        }

        $menu = $this->factory->createItem('picker');

        if ($user->hasAccess('page', 'modules')) {
            $this->addPagePickerItem($menu, $request);
        }

        if ($user->hasAccess('files', 'modules')) {
            $this->addFilePickerItem($menu, $request);
        }

        $this->dispatcher->dispatch(
            ContaoCoreEvents::BUILD_PICKER_MENU,
            new BuildPickerMenuEvent($this->factory, $menu)
        );

        return $this->renderer->render($menu);
    }

    /**
     * Adds the page picker item.
     *
     * @param ItemInterface $menu
     * @param Request       $request
     */
    private function addPagePickerItem(ItemInterface $menu, Request $request)
    {
        $item = $this->factory->createItem(
            $this->pagePickerLabel,
            ['uri' => $this->route('contao_backend', 'page', $request)]
        );

        $item->setLinkAttribute('class', 'pagemounts');

        if ('page' === $request->query->get('do')) {
            $item->setAttribute('class', 'active');
        }

        $menu->addChild($item);
    }

    /**
     * Adds the file picker item.
     *
     * @param ItemInterface $menu
     * @param Request       $request
     */
    private function addFilePickerItem(ItemInterface $menu, Request $request)
    {
        $item = $this->factory->createItem(
            $this->filePickerLabel,
            ['uri' => $this->route('contao_backend', 'files', $request)]
        );

        $item->setLinkAttribute('class', 'filemounts');

        if ('files' === $request->query->get('do')) {
            $item->setAttribute('class', 'active');
        }

        $menu->addChild($item);
    }

    /**
     * Generates a Contao compatible route.
     *
     * @param string  $name
     * @param string  $do
     * @param Request $request
     *
     * @return bool|string
     */
    private function route($name, $do, Request $request)
    {
        $url = $this->router->generate($name, array_merge($request->query->all(), ['do' => $do]));
        $url = substr($url, strlen($request->getBasePath()) + 1);

        return $url;
    }
}
