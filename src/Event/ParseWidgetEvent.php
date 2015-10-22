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
 * Allows to execute logic when a widget is parsed.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ParseWidgetEvent extends Event
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var Widget
     */
    private $widget;

    /**
     * Constructor.
     *
     * @param string $buffer The widget content
     * @param Widget $widget The widget object
     */
    public function __construct($buffer, Widget $widget)
    {
        $this->buffer = $buffer;
        $this->widget = $widget;
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
     * Returns the widget object.
     *
     * @return Widget The widget object
     */
    public function getWidget()
    {
        return $this->widget;
    }
}
