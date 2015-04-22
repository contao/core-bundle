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
 * Provides an adapter for the Contao FrontendCron class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FrontendCronAdapter implements FrontendCronAdapterInterface
{
    /**
     * Run the controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\FrontendCron();
        return $instance->run();
    }
}
