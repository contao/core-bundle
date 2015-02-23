<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Debug;

use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

/**
 * Registers all the debug tools.
 *
 * Heavily based upon the debug class from Symfony.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christian Schiffler <https://github.com/discordier>
 */
class Debug
{
    private static $enabled = false;

    /**
     * @var ErrorHandler
     */
    private static $errorHandler;

    /**
     * @var ExceptionHandler
     */
    private static $exceptionHandler;

    /**
     * Enables the debug tools.
     *
     * This method registers an error handler and an exception handler.
     *
     * If the Symfony ClassLoader component is available, a special
     * class loader is also registered.
     *
     * @param int  $errorReportingLevel The level of error reporting you want
     * @param bool $displayErrors       Whether to display errors (for development) or just log them (for production)
     */
    public static function enable($errorReportingLevel = null, $displayErrors = true)
    {
        if (static::$enabled) {
            return;
        }

        static::$enabled = true;

        if (null !== $errorReportingLevel) {
            error_reporting($errorReportingLevel);
        } else {
            error_reporting(-1);
        }

        if ('cli' !== php_sapi_name()) {
            ini_set('display_errors', 0);
            static::$exceptionHandler = ExceptionHandler::register();
        } elseif ($displayErrors && (!ini_get('log_errors') || ini_get('error_log'))) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
        static::$errorHandler = ErrorHandler::register();
        if (!$displayErrors) {
            static::$errorHandler->throwAt(0, true);
        }

        DebugClassLoader::enable();
    }

    /**
     * Check if the debugger is enabled.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return static::$enabled;
    }

    /**
     * Retrieve the error handler if enabled.
     *
     * @return null|ErrorHandler
     */
    public static function getErrorHandler()
    {
        return isset(static::$errorHandler) ? static::$errorHandler : null;
    }

    /**
     * Retrieve the exception handler if enabled.
     *
     * @return null|ExceptionHandler
     */
    public static function getExceptionHandler()
    {
        return isset(static::$exceptionHandler) ? static::$exceptionHandler : null;
    }

    /**
     * Set the error levels at which errors shall be handled.
     *
     * @param int  $levels  A bit field of E_* constants for thrown errors
     */
    public static function setErrorLevels($levels)
    {
        static::getExceptionHandler()->throwAt($levels, true);
    }
}
