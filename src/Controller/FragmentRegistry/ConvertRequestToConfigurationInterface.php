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

/**
 * Converts a request to a configuration.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ConvertRequestToConfigurationInterface
{
    /**
     * @param Request $request
     *
     * @return ConfigurationInterface|null
     */
    public function convertRequestToConfiguration(Request $request);
}
