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
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var array
     */
    private $row;

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
        $this->row    = &$row;
        $this->module = &$module;
    }

    /**
     * Returns the buffer.
     *
     * @return string The buffer
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Sets the buffer.
     *
     * @param string $buffer The buffer
     */
    public function setBuffer($buffer)
    {
        $this->buffer = $buffer;
    }

    /**
     * Returns the row.
     *
     * @return array The row
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Sets the row.
     *
     * @param array $row The row
     */
    public function setRow($row)
    {
        $this->row = $row;
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
