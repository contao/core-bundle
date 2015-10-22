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
 * Allows to execute logic when a date is parsed.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ParseDateEvent extends Event
{
    /**
     * @var string
     */
    private $return;

    /**
     * @var string
     */
    private $format;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * Constructor.
     *
     * @param string $return    The return value
     * @param string $format    The date format
     * @param int    $timestamp The timestamp
     */
    public function __construct($return, $format, $timestamp)
    {
        $this->return = $return;
        $this->format = $format;
        $this->timestamp = $timestamp;
    }

    /**
     * Returns the return value.
     *
     * @return string The return value
     */
    public function getReturnValue()
    {
        return $this->return;
    }

    /**
     * Sets the return value.
     *
     * @param string $return The return value
     */
    public function setReturnValue($return)
    {
        $this->return = $return;
    }

    /**
     * Returns the date format.
     *
     * @return string The date format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns the timestamp.
     *
     * @return int The timestamp
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
