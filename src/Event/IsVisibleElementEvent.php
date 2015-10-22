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
    private $visible;

    /**
     * @var Model
     */
    private $element;

    /**
     * Constructor.
     *
     * @param bool  $visible The visibility status
     * @param Model $element The element
     */
    public function __construct($visible, Model $element)
    {
        $this->visible = $visible;
        $this->element = $element;
    }

    /**
     * Returns the visibility status.
     *
     * @return bool The visibility status
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Sets the visibility status.
     *
     * @param bool $return The visibility status
     */
    public function setVisible($return)
    {
        $this->visible = (bool) $return;
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
}
