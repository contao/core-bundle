<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

/**
 * Allows to execute logic when an event takes a value and returns it.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReturnValueEvent extends ReadValueEvent
{
    /**
     * Sets the value.
     *
     * @param mixed $value The value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
