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
 * Interface for fragments.
 * See FragmentRegistryInterface for more information.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentInterface
{
    /**
     * Gets the identifier of the fragment. Should be unique across the whole
     * system so use something that ideally includes your vendor name and maybe
     * group them by "type". Examples:
     *
     * - contao.frontend_module.text
     * - contao.content_element.image
     * - company.content_element.improved_image
     * - company.products.list_view
     *
     * @return string
     */
    public static function getIdentifier();

    /**
     * Gets the category of the fragment as a string or null if no categorization
     * is needed.
     *
     * @return string|null
     */
    public static function getCategory();

    /**
     * Checks if the fragment supports a given configuration by the consumer
     * that wants to render the fragment.
     *
     * @param ConfigurationInterface|null $configuration
     *
     * @return bool
     */
    public function supportsConfiguration(ConfigurationInterface $configuration = null);

    /**
     * Gets the fragment render strategy. Symfony core provides "inline",
     * "esi", "ssi" and "hinclude" but everybody can extend the available
     * renderers by using the service tag "kernel.fragment_renderer".
     *
     * @param ConfigurationInterface|null $configuration
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
     * @param ConfigurationInterface|null $configuration
     *
     * @return array
     */
    public function getRenderOptions($configuration = null);

    /**
     * Your fragment likely needs some request query parameters if you use any
     * other render strategy than "inline". Return them here as key->value and
     * you will receive them as query parameters in the renderAction() method.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @param ConfigurationInterface|null $configuration
     *
     * @return array
     */
    public function getQueryParameters(ConfigurationInterface $configuration = null);

    /**
     * @param Request $request
     *
     * @return ConfigurationInterface|null
     */
    public function convertRequestToConfiguration(Request $request);

    /**
     * The render action.
     *
     * @param ConfigurationInterface|null $configuration
     *
     * @return Response
     */
    public function renderAction(ConfigurationInterface $configuration = null);
}
