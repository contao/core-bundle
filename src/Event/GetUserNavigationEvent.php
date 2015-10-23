<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when the back end navigation is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetUserNavigationEvent extends Event
{
    /**
     * @var array
     */
    private $modules;

    /**
     * @var bool
     */
    private $showAll;

    /**
     * Constructor.
     *
     * @param array $modules The modules
     * @param bool  $showAll True to show all menu items
     */
    public function __construct(array $modules, $showAll)
    {
        $this->modules = $modules;
        $this->showAll = $showAll;
    }

    /**
     * Returns the modules.
     *
     * @return array The modules
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Sets the modules.
     *
     * @param array $modules The modules
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Returns the "show all" flag.
     *
     * @return bool The "show all" flag
     */
    public function isShowAll()
    {
        return $this->showAll;
    }
}
