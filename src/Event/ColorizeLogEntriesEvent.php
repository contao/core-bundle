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
 * Allows to execute logic when log entries are colorized.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ColorizeLogEntriesEvent extends Event
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var array
     */
    private $row;

    /**
     * Constructor.
     *
     * @param string $label The label
     * @param array  $row   The row
     */
    public function __construct($label, array &$row)
    {
        $this->label = $label;
        $this->row   = &$row;
    }

    /**
     * Returns the label.
     *
     * @return string The label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label.
     *
     * @param string $label The label
     */
    public function setLabel($label)
    {
        $this->label = $label;
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
    public function setRow(array $row)
    {
        $this->row = $row;
    }
}
