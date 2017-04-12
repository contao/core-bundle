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
    public function supports($table)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($table)) {
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
            if ($provider->supports($table)) {
                return $provider->processSelection($value);
            }
        }

        return $value;
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
        if (isset($params['value'])) {
            foreach ($this->providers as $provider) {
                if ($provider->canHandle($params['value'])) {
                    return $provider->getPickerUrl($params);
                }
            }
        }

        return $this->router->generate('contao_backend', array_merge(['do' => 'page'], $params));
    }
}
