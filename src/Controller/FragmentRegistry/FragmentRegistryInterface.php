<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentType\FragmentInterface;

/**
 * Fragment registry interface.
 *
 * This is an abstraction layer for the Symfony fragment handler.
 * The Symfony fragment handler requires to register real controllers for
 * every single fragment. In Contao we have different fragment types as well
 * as fragments themselves (types are modules, content elements etc. names are
 * e.g. "text", "headline" etc.). It would be very tedious work to register a
 * controller for every single one of them. This abstraction layer allows you
 * to tag your service, implement the FragmentInterface (or a subclass of it)
 * and the rest is taken care of for you.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentRegistryInterface
{
    /**
     * Adds a fragment type.
     *
     * @param string $interfaceClassName
     *
     * @return FragmentRegistryInterface
     */
    public function addFragmentType($interfaceClassName);

    /**
     * Get all fragments types.
     *
     * @return array
     */
    public function getFragmentTypes();

    /**
     * Adds a fragment.
     *
     * @param FragmentInterface $fragment
     *
     * @return FragmentRegistryInterface
     */
    public function addFragment(FragmentInterface $fragment);

    /**
     * Get all fragments, optionally all of a given type.
     *
     * @param string $type
     *
     * @return FragmentInterface[]
     *
     * @throws \InvalidArgumentException If type does not exist.
     */
    public function getFragments($type = '');

    /**
     * Get a fragment by type and name.
     *
     * @param string $type
     * @param string $name
     *
     * @return FragmentInterface
     *
     * @throws \InvalidArgumentException If type or name do not exist.
     */
    public function getFragmentByTypeAndName($type, $name);

    /**
     * Render a fragment based on its type and name, optionally passing
     * configuration to the fragment type and also optionally forcing
     * a render strategy which overrides the one either specified by the
     * type or if the type does not specify one: "inline".
     *
     * @param string      $type
     * @param string      $name
     * @param array       $configuration
     * @param string|null $forceStrategy
     *
     * @return string|null The Response content or null when the Response is streamed
     */
    public function renderFragment($type, $name, array $configuration, $forceStrategy = null);
}
