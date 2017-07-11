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
class PickerFactory implements PickerFactoryInterface
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
    public function createFromPayload($payload)
    {
        $data = @json_decode(base64_decode($payload), true);

        if (null === $data) {
            return null;
        }

        return $this->create(PickerConfig::jsonUnserialize($data));
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialUrl($context, array $extras = [], $value = '')
    {
        $supportsContext = array_reduce(
            $this->providers,
            function ($carry, PickerProviderInterface $provider) use ($context) {
                return true === $carry || $provider->supportsContext($context);
            },
            false
        );

        if (!$supportsContext) {
            return '';
        }

        $extrasString = base64_encode(json_encode($extras));

        return $this->router->generate(
            'contao_backend_picker',
            ['context' => $context, 'extras' => $extrasString, 'value' => $value]
        );
    }
}
