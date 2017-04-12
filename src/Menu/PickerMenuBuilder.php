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
use Knp\Menu\Renderer\RendererInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Creates the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PickerMenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PickerMenuProviderInterface[]
     */
    private $providers = [];

    /**
     * Constructor.
     *
     * @param FactoryInterface  $factory
     * @param RendererInterface $renderer
     * @param RouterInterface   $router
     */
    public function __construct(FactoryInterface $factory, RendererInterface $renderer, RouterInterface $router)
    {
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * Adds a picker menu provider.
     *
     * @param PickerMenuProviderInterface $provider
     */
    public function addProvider(PickerMenuProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Creates the menu.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function createMenu()
    {
        $menu = $this->factory->createItem('picker');

        foreach ($this->providers as $provider) {
            $provider->createMenu($menu, $this->factory);
        }

        return $this->renderer->render($menu);
    }

    /**
     * Checks if a table is supported.
     *
     * @param string $table
     *
     * @return bool
     */
    public function isSupportedTable($table)
    {
        foreach ($this->providers as $provider) {
            if (true === $provider->isSupportedTable($table)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Processes the selected value.
     *
     * @param $table
     * @param $value
     *
     * @return string
     */
    public function processSelection($table, $value)
    {
        foreach ($this->providers as $provider) {
            if (null !== ($processed = $provider->processSelection($table, $value))) {
                return $processed;
            }
        }

        return json_encode(['content' => $value]);
    }

    /**
     * Returns the picker URL.
     *
     * @param array $params
     *
     * @return string
     */
    public function getPickerUrl(array $params = [])
    {
        if (!isset($params['do'])) {
            $params = array_merge(['do' => null], $params);
        }

        foreach ($this->providers as $provider) {
            if (null !== ($url = $provider->getPickerUrl($params))) {
                return $url;
            }
        }

        // Fall back to the page picker
        if (null === $params['do']) {
            $params['do'] = 'page';
        }

        return $this->router->generate('contao_backend', $params);
    }
}
