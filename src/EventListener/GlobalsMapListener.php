<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;

class GlobalsMapListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var array
     */
    private $globals = [];

    /**
     * Constructor.
     *
     * @param array $globals
     */
    public function __construct(array $globals)
    {
        $this->globals = $globals;
    }

    /**
     * Maps fragments to the globals array.
     */
    public function onInitializeSystem(): void
    {
        $this->framework->initialize();

        $GLOBALS = array_merge_recursive($GLOBALS, $this->globals);
    }
}
