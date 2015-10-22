<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when checking an element for visibility.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class IsVisibleElementEvent extends Event
{
    /**
     * @var bool
     */
    private $return;

    /**
     * @var Model
     */
    private $element;

    /**
     * Constructor.
     *
     * @param bool  $return  The return value
     * @param Model $element The element
     */
    public function __construct($return, Model &$element)
    {
        $this->return = $return;
        $this->element = &$element;
    }

    /**
     * Returns the return value.
     *
     * @return bool The return value
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Sets the return value.
     *
     * @param bool $return The return value
     */
    public function setReturn($return)
    {
        $this->return = (bool) $return;
    }

    /**
     * Returns the element.
     *
     * @return Model The element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Sets the element.
     *
     * @param Model $element The element
     */
    public function setElement(Model $element)
    {
        $this->element = $element;
    }
}
