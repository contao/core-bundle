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
 * Provides an adapter for the Contao BackendInstall class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendInstallAdapter implements BackendInstallAdapterInterface
{
    /**
     * Run the controller and parse the login template
     */
    public function run()
    {
        $instance = new \Contao\BackendInstall();
        $instance->run();
    }
}
