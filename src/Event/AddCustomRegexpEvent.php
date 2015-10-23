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
     * @var string
     */
    private $regexName;

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
     * @param string $regexName The regular expression name
     * @param mixed  $input     The user input
     * @param Widget $widget    The widget object
     */
    public function __construct($regexName, $input, Widget $widget)
    {
        $this->regexName = $regexName;
        $this->input = $input;
        $this->widget = $widget;
    }

    /**
     * Returns the regular expression name.
     *
     * @return string The regular expression name
     */
    public function getRegexName()
    {
        return $this->regexName;
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
     * Returns the widget object.
     *
     * @return Widget The widget object
     */
    public function getWidget()
    {
        return $this->widget;
    }
}
