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
 * Fragment registry interface.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentRegistryInterface
{
    /**
     * Adds a fragment.
     *
     * @param FragmentInterface $fragment
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
}
