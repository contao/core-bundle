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
 * Provides an adapter for the Contao BackendConfirm class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class BackendConfirmAdapter implements BackendConfirmAdapterInterface
{
    /**
     * Run the controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\BackendConfirm();
        return $instance->run();
    }
}
