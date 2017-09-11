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
     * @param string            $identifier
     * @param FragmentInterface $fragment
     *
     * @return FragmentRegistryInterface
     */
    public function addFragment($identifier, FragmentInterface $fragment);

    /**
     * Gets a fragment by its identifier.
     *
     * @param string $identifier
     *
     * @return FragmentInterface|null
     */
    public function getFragment($identifier);

    /**
     * Gets options for a fragment.
     *
     * @param string $identifier
     *
     * @return array
     */
    public function getOptions($identifier);

    /**
     * Gets an array of fragments that match a certain option value.
     * E.g. getFragmentsByOptionValue('type', 'foobar').
     *
     * @param string $key
     * @param string $value
     *
     * @return FragmentInterface[]
     */
    public function getFragmentsByOptionValue($key, $value);

    /**
     * Renders a fragment and returns a string (response content according
     * to the render strategy) or null (when the response is streamed).
     * By default, any fragment is rendered using the "inline" strategy for
     * maximum compatibility. Of course, other strategies such as "esi" or
     * "hinclude" (or your own one) are the interesting ones.
     *
     * @param FragmentInterface $fragment
     * @param array             $configuration
     * @param string            $renderStrategy
     * @param array             $renderOptions
     *
     * @return null|string
     */
    public function renderFragment(FragmentInterface $fragment, array $configuration = [], $renderStrategy = 'inline', $renderOptions = []);
}
