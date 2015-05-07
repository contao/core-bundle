<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\Widget;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a custom regular expression is found.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddCustomRegexpEvent extends Event
{
    /**
     * @var bool
     */
    private $break;

    /**
     * @var string
     */
    private $rgxp;

    /**
     * @var mixed
     */
    private $input;

    /**
     * @var Widget
     */
    private $widget;

    /**
     * Constructor.
     *
     * @param string $rgxp   The regular expression name
     * @param mixed  $input  The user input
     * @param Widget $widget The widget object
     */
    public function __construct(&$rgxp, &$input, Widget &$widget)
    {
        $this->rgxp   = &$rgxp;
        $this->input  = &$input;
        $this->widget = &$widget;
    }

    /**
     * Returns the "break" flag.
     *
     * @return boolean The "break" flag
     */
    public function getBreak()
    {
        return $this->break;
    }

    /**
     * Sets the "break" flag.
     *
     * @param bool $break The "break" flag
     */
    public function setBreak($break)
    {
        $this->break = (bool) $break;
    }

    /**
     * Returns the regular expression name.
     *
     * @return string The regular expression name
     */
    public function getRgxp()
    {
        return $this->rgxp;
    }

    /**
     * Sets the regular expression name.
     *
     * @param string $rgxp The regular expression name
     */
    public function setRgxp($rgxp)
    {
        $this->rgxp = $rgxp;
    }

    /**
     * Returns the user input.
     *
     * @return mixed The user input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Sets the user input.
     *
     * @param mixed $input The user input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Returns the widget object.
     *
     * @return Widget The widget object
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Sets the widget object.
     *
     * @param Widget $widget The widget object
     */
    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;
    }
}
