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
 * Adds a $buffer property with getters and setters.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait GetBufferTrait
{
    /**
     * @var string
     */
    private $buffer;

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
}
