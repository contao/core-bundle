<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for fragment configuration.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ConfigurationInterface
{
    /**
     * Gets the fragment render strategy. Symfony core provides "inline",
     * "esi", "ssi" and "hinclude" but everybody can extend the available
     * renderes by using the service tag "kernel.fragment_renderer".
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @return string
     */
    public function getRenderStrategy();

    /**
     * Gets the render options for the render strategy. Most of the times
     * this is an empty array. Some strategies don't even support options but
     * some (e.g. like ESI) do to add e.g. comments to the <esi> tag.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     */
    public function getRenderOptions();

    /**
     * Your fragment likely needs some request query parameters if you use any
     * other render strategy than "inline". Return them here as key->value and
     * you will receive them as query parameters in the renderAction() method.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @return array
     */
    public function getQueryParameters();

    /**
     * Get arbitrary attributes.
     *
     * @return array
     */
    public function getAttributes();
}
