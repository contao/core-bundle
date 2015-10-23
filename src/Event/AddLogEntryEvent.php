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
 * Allows to execute logic when a log entry is added.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class AddLogEntryEvent extends Event
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $function;

    /**
     * @var string
     */
    private $category;

    /**
     * Constructor.
     *
     * @param string $text     The log message
     * @param string $function The function name
     * @param string $category The category
     */
    public function __construct($text, $function, $category)
    {
        $this->text = $text;
        $this->function = $function;
        $this->category = $category;
    }

    /**
     * Returns the text.
     *
     * @return string The text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the function name.
     *
     * @return string The function name
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Returns the category.
     *
     * @return string The category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
