<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FragmentRegistry implements FragmentRegistryInterface
{
    /**
     * @var FragmentHandler
     */
    private $fragmentHandler;

    /**
     * @var string
     */
    private $controllerName;

    /**
     * @var FragmentInterface[]
     */
    private $fragments = [];

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * An array with interface as key and
     * fragments as values.
     *
     * @var array
     */
    private $interfacesToFragmentCache = [];

    /**
     * FragmentRegistry constructor.
     *
     * @param FragmentHandler $fragmentHandler
     * @param string          $controllerName
     */
    public function __construct(FragmentHandler $fragmentHandler, $controllerName)
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->controllerName = $controllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function addFragment(FragmentInterface $fragment)
    {
        if ($this->isInitialized) {
            throw new \BadMethodCallException('You cannot add fragments if the fragment registry was already initialized!');
        }

        // Overrides existing fragments with same identifier
        $this->fragments[$fragment->getIdentifier()] = $fragment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments(array $mustImplementInterfaces)
    {
        $this->initialize();

        $matches = [];

        foreach ($mustImplementInterfaces as $mustImplementInterface) {
            if (isset($this->interfacesToFragmentCache[$mustImplementInterface])) {
                $matches = array_merge($matches, $this->interfacesToFragmentCache[$mustImplementInterface]);
            }
        }

        return array_unique($matches);
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment($identifier)
    {
        $this->initialize();

        return $this->fragments[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFragment(FragmentInterface $fragment, $configuration = null, RenderStrategyInterface $overridingRenderStrategy = null)
    {
        if (!$fragment->supportsConfiguration($configuration)) {
            throw new \InvalidArgumentException(
                sprintf('The fragment "%s" does not support the given configuration.', $fragment->getIdentifier())
            );
        }

        $renderStrategy = 'inline';
        $renderOptions = [];
        $queryParameters = [];

        if ($fragment instanceof RenderStrategyInterface) {
            $renderStrategy = $fragment->getRenderStrategy($configuration);
            $renderOptions = $fragment->getRenderOptions($configuration);
        }

        if (null !== $overridingRenderStrategy) {
            $renderStrategy = $overridingRenderStrategy->getRenderStrategy($configuration);
            $renderOptions = $overridingRenderStrategy->getRenderOptions($configuration);
        }

        if ($fragment instanceof QueryParameterProviderInterface) {
            $queryParameters = $fragment->getQueryParameters($configuration);
        }

        $uri = new ControllerReference(
            $this->controllerName, [
                '_fragment_identifier' => $fragment->getIdentifier(),
            ], $queryParameters
        );

        return $this->fragmentHandler->render(
            $uri,
            $renderStrategy,
            $renderOptions
        );
    }

    /**
     * Initialize fragments.
     */
    private function initialize()
    {
        foreach ($this->fragments as $fragment) {
            $ref = new \ReflectionClass($fragment);

            foreach ($ref->getInterfaceNames() as $interfaceName) {
                if (!isset($this->interfacesToFragmentCache[$interfaceName])) {
                    $this->interfacesToFragmentCache[$interfaceName] = [];
                }

                $this->interfacesToFragmentCache[$interfaceName][] = $fragment;
            }
        }

        $this->isInitialized = true;
    }
}
