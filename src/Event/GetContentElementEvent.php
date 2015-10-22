<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\ContentElement;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a content element is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetContentElementEvent extends Event
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
     * @var ContentElement
     */
    private $element;

    /**
     * Constructor.
     *
     * @param string         $buffer  The buffer
     * @param array          $row     The row
     * @param ContentElement $element The content element
     */
    public function __construct($buffer, array $row, ContentElement $element)
    {
        $this->buffer = $buffer;
        $this->row = $row;
        $this->element = $element;
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
     * Returns the content element.
     *
     * @return ContentElement The content element
     */
    public function getElement()
    {
        return $this->element;
    }
}
