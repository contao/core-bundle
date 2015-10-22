<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\Module;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a front end module is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetFrontendModuleEvent extends Event
{
    use BufferAwareTrait;
    use RowAwareTrait;

    /**
     * @var Module
     */
    private $module;

    /**
     * Constructor.
     *
     * @param string $buffer The buffer
     * @param array  $row    The row
     * @param Module $module The module
     */
    public function __construct($buffer, array &$row, Module &$module)
    {
        $this->buffer = $buffer;
        $this->row = &$row;
        $this->module = &$module;
    }

    /**
     * Returns the module.
     *
     * @return Module The module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Sets the module.
     *
     * @param Module $module The module
     */
    public function setModule(Module $module)
    {
        $this->module = $module;
    }
}
