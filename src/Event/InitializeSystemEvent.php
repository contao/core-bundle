<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Allows to execute logic when the system is initialized.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class InitializeSystemEvent extends Event
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * Constructor.
     *
     * @param string $rootDir The root directory
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Returns the root directory.
     *
     * @return string The root directory
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }
}
