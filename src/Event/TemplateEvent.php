<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\Template;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a template is parsed.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TemplateEvent extends Event
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var string
     */
    private $key;

    /**
     * @var Template
     */
    private $template;

    /**
     * Constructor.
     *
     * @param string        $buffer   The template content
     * @param string        $key      The template key
     * @param Template|null $template An optional template object
     */
    public function __construct($buffer, $key, Template $template = null)
    {
        $this->buffer = $buffer;
        $this->key = $key;
        $this->template = $template;
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
     * Returns the template key.
     *
     * @return string The template key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the template object.
     *
     * @return Template The template object
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
