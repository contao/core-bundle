<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Adapter;

/**
 * Provides an adapter for the Contao BackendPassword class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendPasswordAdapter implements BackendPasswordAdapterInterface
{
    /**
     * Run the controller and parse the password template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\BackendPassword();
        return $instance->run();
    }
}
