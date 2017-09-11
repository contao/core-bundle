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
     * @var array
     */
    private $cache = [];

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
        if (0 !== count($this->cache)) {
            throw new \BadMethodCallException('You cannot add fragments if the fragment registry was already initialized!');
        }

        // Overrides existing fragments with same identifier
        $this->fragments[$fragment::getIdentifier()] = $fragment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragments(array $mustImplementInterfaces)
    {
        sort($mustImplementInterfaces);

        $key = md5(implode(',', $mustImplementInterfaces));

        if (isset($this->cache[$key])) {

            return $this->cache[$key];
        }

        $matches = [];
        $visitedFragmentClassNames = [];
        foreach ($this->fragments as $fragment) {
            $ref = new \ReflectionClass($fragment);

            foreach ($mustImplementInterfaces as $mustImplementInterface) {
                if (!$ref->implementsInterface($mustImplementInterface)) {
                    continue 2;
                }

                if (!isset($visitedFragmentClassNames[$ref->getName()])) {
                    $matches[] = $fragment;
                    $visitedFragmentClassNames[$ref->getName()] = null;
                }
            }
        }

        $this->cache[$key] = $matches;

        return $matches;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment($identifier)
    {
        return $this->fragments[$identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function renderFragment(FragmentInterface $fragment, ConfigurationInterface $configuration = null, RenderStrategy $overridingRenderStrategy = null)
    {
        if (!$fragment->supportsConfiguration($configuration)) {
            $exception = new InvalidConfigurationException(
                sprintf('The fragment "%s" does not support the given configuration.', $fragment::getIdentifier())
            );
            $exception->setConfiguration($configuration);
            throw $exception;
        }

        $renderStrategy = $this->determineRenderStrategy($fragment, $configuration, $overridingRenderStrategy);

        $uri = new ControllerReference(
            $this->controllerName, [
                '_fragment_identifier' => $fragment::getIdentifier(),
            ], $fragment->getQueryParameters($configuration)
        );

        return $this->fragmentHandler->render(
            $uri,
            $renderStrategy->getRenderStrategy(),
            $renderStrategy->getRenderOptions()
        );
    }

    /**
     * Determines the render strategy.
     *
     * @param FragmentInterface      $fragment
     * @param ConfigurationInterface $configuration
     * @param RenderStrategy|null    $overridingRenderStrategy
     *
     * @return RenderStrategy
     */
    private function determineRenderStrategy(FragmentInterface $fragment, ConfigurationInterface $configuration = null, RenderStrategy $overridingRenderStrategy = null)
    {
        $strategy = new RenderStrategy();
        $strategy->setRenderStrategy($fragment->getRenderStrategy($configuration));
        $strategy->setRenderOptions($fragment->getRenderOptions($configuration));

        if (null !== $overridingRenderStrategy) {
            $strategy->setRenderStrategy($overridingRenderStrategy->getRenderStrategy($configuration));
            $strategy->setRenderOptions($overridingRenderStrategy->getRenderOptions($configuration));
        }

        return $strategy;
    }
}
