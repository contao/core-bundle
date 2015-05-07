<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Traits;

/**
 * Adds a $row property with getters and setters.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait GetRowTrait
{
    /**
     * @var array
     */
    private $row;

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
