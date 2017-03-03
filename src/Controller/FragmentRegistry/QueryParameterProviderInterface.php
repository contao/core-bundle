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
 * Classes implementing this interface define their query parameters.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface QueryParameterProviderInterface
{
    /**
     * Your fragment likely needs some request query parameters if you use any
     * other render strategy than "inline". Return them here as key->value and
     * you will receive them as query parameters in the renderAction() method.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @param mixed $configuration Optional configuration
     *
     * @return array
     */
    public function getQueryParameters($configuration = null);
}
