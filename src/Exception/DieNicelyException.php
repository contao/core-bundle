<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Exception;

/**
 * Exception to trigger the "beautiful" error screen.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class DieNicelyException extends \RuntimeException
{
    /**
     * The template to use.
     *
     * @var string
     */
    private $template;

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @param string     $template The backend template to be shown for this exception.
     * @param string     $message  The Exception message to throw.
     * @param int        $code     [optional] The Exception code.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     */
    public function __construct($template, $message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->template = $template;
    }

    /**
     * Retrieve the template to display instead of the exception message.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
