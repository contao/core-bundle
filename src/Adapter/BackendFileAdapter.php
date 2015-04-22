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
 * Provides an adapter for the Contao BackendFile class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendFileAdapter implements BackendFileAdapterInterface
{
    /**
     * Run the controller and parse the template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\BackendFile();
        return $instance->run();
    }
}
