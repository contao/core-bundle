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
 * Classes implementing this interface define their fragment rendering strategy.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface RenderStrategyInterface
{
    /**
     * Gets the fragment render strategy. Symfony core provides "inline",
     * "esi", "ssi" and "hinclude" but everybody can extend the available
     * renderers by using the service tag "kernel.fragment_renderer".
     *
     * @param mixed $configuration Optional configuration
     *
     * @return string
     */
    public function getRenderStrategy($configuration = null);

    /**
     * Gets the render options for the render strategy. Most of the times
     * this is an empty array. Some strategies don't even support options but
     * some (e.g. like "esi") do to add e.g. comments to the <esi> tag.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     *
     * @param mixed $configuration Optional configuration
     *
     * @return array
     */
    public function getRenderOptions($configuration = null);
}
