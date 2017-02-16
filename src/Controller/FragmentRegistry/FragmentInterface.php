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
     * Gets the name of the fragment. Should be unique accross the whole
     * system so use something that ideally includes your vendor name like
     * "contao.text".
     *
     * @return string
     */
    public function getName();

    /**
     * Allows the fragment to apply changes on the configuration.
     *
     * @param ConfigurationInterface $configuration
     */
    public function modifyConfiguration(ConfigurationInterface $configuration);

    /**
     * The render action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function renderAction(Request $request);
}
