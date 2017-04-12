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
        $user = $this->getUser();

        if ($user->hasAccess('page', 'modules')) {
            $this->addMenuItem($menu, $factory, 'page', 'pagePicker', 'pagemounts');
        }

        if ($user->hasAccess('files', 'modules')) {
            $this->addMenuItem($menu, $factory, 'files', 'filePicker', 'filemounts');
        }

        if ($user->hasAccess('article', 'modules')) {
            $this->addMenuItem($menu, $factory, 'article', 'articlePicker', 'articles');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedTable($table)
    {
        return in_array($table, ['tl_page', 'tl_files', 'tl_article']);
    }

    /**
     * {@inheritdoc}
     */
    public function processSelection($table, $value)
    {
        switch ($table) {
            case 'tl_page':
                return sprintf('{{link_url::%s}}', $value);

            case 'tl_files':
                return $value;

            case 'tl_article':
                return sprintf('{{article_url::%s}}', $value);

            default:
                return null;
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

            case false !== strpos($params['value'], '{{article_url::'):
                $params['do'] = 'article';
                $params['value'] = str_replace(['{{article_url::', '}}'], '', $params['value']);
                break;

            default:
                return null;
        }

        return $this->route('contao_backend', $params);
    }
}
