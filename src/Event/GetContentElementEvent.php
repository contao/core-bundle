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
use Contao\CoreBundle\Traits\GetBufferTrait;
use Contao\CoreBundle\Traits\GetRowTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a content element is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetContentElementEvent extends Event
{
    use GetBufferTrait;
    use GetRowTrait;

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
    public function __construct($buffer, array &$row, ContentElement &$element)
    {
        $this->buffer  = $buffer;
        $this->row     = &$row;
        $this->element = &$element;
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

    /**
     * Sets the content element.
     *
     * @param ContentElement $element The content element
     */
    public function setElement(ContentElement $element)
    {
        $this->element = $element;
    }
}
