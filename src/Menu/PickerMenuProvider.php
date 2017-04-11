<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds the page and file picker to the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PickerMenuProvider extends AbstractMenuProvider implements PickerMenuProviderInterface
{
    /**
     * @var string
     */
    private $uploadPath;

    /**
     * Constructor.
     *
     * @param RouterInterface       $router
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack          $requestStack
     * @param string                $uploadPath
     */
    public function __construct(RouterInterface $router, TokenStorageInterface $tokenStorage, RequestStack $requestStack, $uploadPath)
    {
        parent::__construct($router, $tokenStorage, $requestStack);

        $this->uploadPath = $uploadPath;
    }

    /**
     * {@inheritdoc}
     */
    public function createMenu(ItemInterface $menu, FactoryInterface $factory)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $user = $this->getUser();
        $params = $request->query->all();

        if ($user->hasAccess('page', 'modules')) {
            $this->addPagePickerItem($menu, $factory, $params);
        }

        if ($user->hasAccess('files', 'modules')) {
            $this->addFilePickerItem($menu, $factory, $params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPickerUrl(array $params = [])
    {
        if (empty($params['value'])) {
            return null;
        }

        switch (true) {
            case false !== strpos($params['value'], '{{link_url::'):
                $params['do'] = 'page';
                $params['value'] = str_replace(['{{link_url::', '}}'], '', $params['value']);
                break;

            case 0 === strpos($params['value'], $this->uploadPath.'/'):
                $params['do'] = 'files';
                break;

            default:
                return null;
        }

        return $this->route('contao_backend', $params);
    }

    /**
     * Adds the page picker item.
     *
     * @param ItemInterface    $menu
     * @param FactoryInterface $factory
     * @param array            $params
     */
    private function addPagePickerItem(ItemInterface $menu, FactoryInterface $factory, array $params)
    {
        $item = $factory->createItem(
            $this->getLabel('pagePicker'),
            ['uri' => $this->route('contao_backend', array_merge($params, ['do' => 'page']))]
        );

        $item->setLinkAttribute('class', 'pagemounts');

        if (isset($params['do']) && 'page' === $params['do']) {
            $item->setCurrent(true);
        }

        $menu->addChild($item);
    }

    /**
     * Adds the file picker item.
     *
     * @param ItemInterface    $menu
     * @param FactoryInterface $factory
     * @param array            $params
     */
    private function addFilePickerItem(ItemInterface $menu, FactoryInterface $factory, array $params)
    {
        $item = $factory->createItem(
            $this->getLabel('filePicker'),
            ['uri' => $this->route('contao_backend', array_merge($params, ['do' => 'files']))]
        );

        $item->setLinkAttribute('class', 'filemounts');

        if (isset($params['do']) && 'files' === $params['do']) {
            $item->setCurrent(true);
        }

        $menu->addChild($item);
    }
}
