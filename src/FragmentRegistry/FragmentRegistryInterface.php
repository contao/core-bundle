<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry;

/**
 * Fragment registry.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentRegistryInterface
{
    /**
     * Adds a fragment.
     *
     * If a fragment with the same identifier already exists, it will override
     * the old one.
     * The $options array must at least handle the following three keys:
     *     - tag (which contains the fragment tag (e.g. "contao.fragment.frontend_module")
     *     - type (which contains the type within that fragment type (e.g. "navigation")
     *     - controller (which contains the controller reference to that fragment)
     *
     * @param string $identifier
     * @param        $fragment
     * @param array  $options
     *
     * @return FragmentRegistryInterface
     */
    public function addFragment(string $identifier, $fragment, array $options): FragmentRegistryInterface;

    /**
     * Gets a fragment by its identifier.
     *
     * @param string $identifier
     *
     * @return object|null
     */
    public function getFragment($identifier);

    /**
     * Gets an array of fragments that optionally match a given filter
     * callable which receives the identifier as first parameter and the
     * fragment instance as second.
     *
     * @param callable|null $filter
     *
     * @return object[]
     *
     */
    public function getFragments(callable $filter = null): array;

    /**
     * Gets options for a fragment.
     *
     * @param string $identifier
     *
     * @return array
     */
    public function getOptions($identifier): array;
}
