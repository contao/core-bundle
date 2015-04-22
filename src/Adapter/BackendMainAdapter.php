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
 * Provides an adapter for the Contao BackendMain class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendMainAdapter implements BackendMainAdapterInterface
{
    /**
     * Run the controller and parse the login template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\BackendMain();
        return $instance->run();
    }
}
