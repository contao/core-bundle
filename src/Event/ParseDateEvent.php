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
 * Allows to execute logic when a date is parsed.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ParseDateEvent extends ReturnValueEvent
{
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
     * @param string $value     The return value
     * @param string $format    The date format
     * @param int    $timestamp The timestamp
     */
    public function __construct($value, $format, $timestamp)
    {
        parent::__construct($value);

        $this->format = $format;
        $this->timestamp = $timestamp;
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
