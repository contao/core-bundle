<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\BuildPickerMenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds the page and file picker to the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BuildPickerMenuListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param RouterInterface       $router
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack          $requestStack
     */
    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    /**
     * Adds the page and file picker to the picker menu.
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

    /**
     * Returns the back end user object.
     *
     * @return BackendUser
     *
     * @throws \RuntimeException
     */
    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (null === $user) {
            throw new \RuntimeException('The token does not contain a user');
        }

        return $user;
    }

    /**
     * Returns a label.
     *
     * @param $key
     *
     * @return string
     */
    private function getLabel($key)
    {
        if (isset($GLOBALS['TL_LANG']['MSC'][$key])) {
            return $GLOBALS['TL_LANG']['MSC'][$key];
        }

        return $key;
    }

    /**
     * Generates a Contao compatible route.
     *
     * @param string  $name
     * @param string  $do
     *
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
