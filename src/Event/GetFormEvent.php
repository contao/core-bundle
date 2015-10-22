<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\Form;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a form is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetFormEvent extends Event
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
     * @var Form
     */
    private $form;

    /**
     * Constructor.
     *
     * @param string $buffer The buffer
     * @param array  $row    The row
     * @param Form   $form   The form object
     */
    public function __construct($buffer, array $row, Form $form)
    {
        $this->buffer = $buffer;
        $this->row = $row;
        $this->form = $form;
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
     * Returns the form object.
     *
     * @return Form The form object
     */
    public function getForm()
    {
        return $this->form;
    }
}
