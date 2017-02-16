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
 * Interface for fragment type providers
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentTypesProviderInterface
{
    /**
     * Returns an array of interface names as key and their respective DI service tag
     * as value.
     *
     * @return array
     */
    public function getFragmentTypes();
}
