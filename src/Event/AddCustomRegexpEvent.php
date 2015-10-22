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
    private $name;

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
     * @param string $name   The regular expression name
     * @param mixed  $input  The user input
     * @param Widget $widget The widget object
     */
    public function __construct($name, $input, Widget $widget)
    {
        $this->name = $name;
        $this->input = $input;
        $this->widget = $widget;
    }

    /**
     * Returns the regular expression name.
     *
     * @return string The regular expression name
     */
    public function getName()
    {
        return $this->name;
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
