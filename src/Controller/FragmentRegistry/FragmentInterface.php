<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Interface for fragments.
 * See FragmentRegistryInterface for more information.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentInterface
{
    /**
     * Returns the controller reference for that fragment.
     *
     * @param array $configuration
     *
     * @return ControllerReference
     */
    public function getControllerReference(array $configuration);
}
