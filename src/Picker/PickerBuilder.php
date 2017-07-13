<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Picker factory.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class PickerBuilder implements PickerBuilderInterface
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var PickerProviderInterface[]
     */
    private $providers = [];

    /**
     * Constructor.
     *
     * @param FactoryInterface $menuFactory
     * @param RouterInterface  $router
     * @param RequestStack     $requestStack
     */
    public function __construct(FactoryInterface $menuFactory, RouterInterface $router, RequestStack $requestStack)
    {
        $this->menuFactory = $menuFactory;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Adds picker providers to the factory.
     *
     * @param PickerProviderInterface $provider
     */
    public function addProvider(PickerProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function create(PickerConfig $config)
    {
        $providers = array_filter(
            $this->providers,
            function (PickerProviderInterface $provider) use ($config) {
                return $provider->supportsContext($config->getContext());
            }
        );

        if (empty($providers)) {
            return null;
        }

        return new Picker(
            $this->menuFactory,
            $providers,
            $config
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createFromData($data)
    {
        try {
            $config = PickerConfig::urlDecode($data);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $this->create($config);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supportsContext($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($context, array $extras = [], $value = '')
    {
        if (!$this->supportsContext($context)) {
            return '';
        }

        return $this->router->generate(
            'contao_backend_picker',
            ['context' => $context, 'extras' => $extras, 'value' => $value]
        );
    }
}
