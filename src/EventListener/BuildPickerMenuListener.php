<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Event\BuildPickerMenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds the page and file picker to the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BuildPickerMenuListener extends AbstractBuildMenuListener
{
    /**
     * Handles the onCreateMenu event.
     *
     * @param BuildPickerMenuEvent $event
     */
    public function onCreateMenu(BuildPickerMenuEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $menu = $event->getMenu();
        $factory = $event->getFactory();
        $user = $this->getUser();

        if ($user->hasAccess('page', 'modules')) {
            $this->addPagePickerItem($menu, $factory, $request);
        }

        if ($user->hasAccess('files', 'modules')) {
            $this->addFilePickerItem($menu, $factory, $request);
        }
    }

    /**
     * Adds the page picker item.
     *
     * @param ItemInterface    $menu
     * @param FactoryInterface $factory
     * @param Request          $request
     */
    private function addPagePickerItem(ItemInterface $menu, FactoryInterface $factory, Request $request)
    {
        $item = $factory->createItem(
            $this->getLabel('pagePicker'),
            ['uri' => $this->route('contao_backend', 'page', $request)]
        );

        $item->setLinkAttribute('class', 'pagemounts');

        if ('page' === $request->query->get('do')) {
            $item->setCurrent(true);
        }

        $menu->addChild($item);
    }

    /**
     * Adds the file picker item.
     *
     * @param ItemInterface    $menu
     * @param FactoryInterface $factory
     * @param Request          $request
     */
    private function addFilePickerItem(ItemInterface $menu, FactoryInterface $factory, Request $request)
    {
        $item = $factory->createItem(
            $this->getLabel('filePicker'),
            ['uri' => $this->route('contao_backend', 'files', $request)]
        );

        $item->setLinkAttribute('class', 'filemounts');

        if ('files' === $request->query->get('do')) {
            $item->setCurrent(true);
        }

        $menu->addChild($item);
    }
}
