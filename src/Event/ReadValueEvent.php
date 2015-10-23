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
 * Allows to execute logic when an event takes a value but does not return it.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReadValueEvent extends Event
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param mixed $value The value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Returns the value.
     *
     * @return mixed The value
     */
    public function getValue()
    {
        return $this->value;
    }
}
