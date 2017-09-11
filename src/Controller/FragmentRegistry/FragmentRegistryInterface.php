<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentRegistryInterface
{
    /**
     * Adds a fragment.
     * If a fragment with the same identifier already exists, it will override
     * the old one.
     *
     * @param FragmentInterface $fragment
     *
     * @return FragmentRegistryInterface
     */
    public function addFragment(FragmentInterface $fragment);

    /**
     * Gets an array of fragments that implement a given array of interfaces.
     * If you specify multiple interfaces, only fragments implementing ALL of
     * them are returned.
     *
     * @param array $mustImplementInterfaces
     *
     * @return FragmentInterface[]
     */
    public function getFragments(array $mustImplementInterfaces);

    /**
     * Gets a fragment by its identifier.
     *
     * @param string $identifier
     *
     * @return FragmentInterface|null
     */
    public function getFragment($identifier);

    /**
     * Renders a fragment optionally passing on arbitrary configuration.
     * The fragment is asked if it supports() the configuration and if not,
     * an InvalidConfigurationException is thrown.
     * Otherwise a string (response content according to the render
     * strategy) or null (when the response is streamed) is returned.
     * By default, any fragment is rendered using the "inline" strategy for
     * maximum compatibility. Of course, other strategies such as "esi" or
     * "hinclude"
     * (or your own one) are the interesting ones. The fragment can implement
     * the StrategyProvidingInterface and define its own default. You can then
     * still override it by passing on the strategy as third argument to this
     * method.
     *
     * @param FragmentInterface       $fragment
     * @param ConfigurationInterface  $configuration
     * @param RenderStrategy          $overridingRenderStrategy
     *
     * @return null|string
     *
     * @throws InvalidConfigurationException
     */
    public function renderFragment(FragmentInterface $fragment, ConfigurationInterface $configuration = null, RenderStrategy $overridingRenderStrategy = null);
}
