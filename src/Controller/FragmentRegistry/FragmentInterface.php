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
     * Checks if the fragment supports a given configuration by the consumer
     * that wants to render the fragment.
     *
     * @param ConfigurationInterface $configuration
     *
     * @return bool
     */
    public function supportsConfiguration(ConfigurationInterface $configuration);

    /**
     * The render action.
     *
     * @param ConfigurationInterface $configuration
     *
     * @return Response
     */
    public function renderAction(ConfigurationInterface $configuration = null);
}
