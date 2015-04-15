<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\Hook;

/**
 * Parent class for hook listeners.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class AbstractHookListener
{
    /**
     * Returns the hook name.
     *
     * @return string The hook name
     */
    abstract protected function getHookName();

    /**
     * Returns the registered callbacks of a hook.
     *
     * @return array The registered callbacks
     */
    protected function getCallbacks()
    {
        $hookName = $this->getHookName();

        if (!isset($GLOBALS['TL_HOOKS'][$hookName]) || !is_array($GLOBALS['TL_HOOKS'][$hookName])) {
            return [];
        }

        return $GLOBALS['TL_HOOKS'][$hookName];
    }

    /**
     * Converts a callback to a callable.
     *
     * @param array|callable $callback The callback
     *
     * @return array|callable The callable
     *
     * @throws \InvalidArgumentException If the callback has an invalid format
     */
    protected function getCallable($callback)
    {
        // Closure
        if (is_object($callback) && is_callable($callback)) {
            return $callback;
        }

        // Check for an array with two members
        if (!is_array($callback) || count($callback) !== 2) {
            throw new \InvalidArgumentException("$callback is not a valid callback.");
        }

        $class = new \ReflectionClass($callback[0]);

        // Static method
        if ($class->hasMethod($callback[1]) && $class->getMethod($callback[1])->isStatic()) {
            return $callback;
        }

        // Singleton
        if ($class->hasMethod('getInstance') && $class->getMethod('getInstance')->isStatic()) {
            return [$callback[0]::getInstance(), $callback[1]];
        }

        // Regular object
        return [new $callback[0](), $callback[1]];
    }
}
