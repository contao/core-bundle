<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\CoreBundle\Traits\GetBufferTrait;
use Contao\CoreBundle\Traits\GetRowTrait;
use Contao\Form;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a form is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetFormEvent extends Event
{
    use GetBufferTrait;
    use GetRowTrait;

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
    public function __construct($buffer, array &$row, Form &$form)
    {
        $this->buffer = $buffer;
        $this->row    = &$row;
        $this->form   = &$form;
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

    /**
     * Sets the form object.
     *
     * @param Form $form The form object
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    }
}
