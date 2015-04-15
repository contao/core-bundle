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
     */
    protected function getCallable($callback)
    {
        if (is_object($callback) && is_callable($callback)) {
            return $callback;
        }

        return $this->getCallableFromArray($callback);
    }

    /**
     * Converts a callback array to a callable.
     *
     * @param array|callable $callback The callback
     *
     * @return array The callable array
     *
     * @throws \InvalidArgumentException If the callback has an invalid format
     */
    private function getCallableFromArray(array $callback)
    {
        if (count($callback) !== 2) {
            throw new \InvalidArgumentException("$callback is not a valid callback.");
        }

        if (null !== ($callable = $this->getCallableFromStaticMethod($callback))) {
            return $callable;
        }

        if (null !== ($callable = $this->getCallableFromSingleton($callback))) {
            return $callable;
        }

        if (null !== ($callable = $this->getCallableFromRegularObject($callback))) {
            return $callable;
        }

        throw new \InvalidArgumentException("$callback cannot be converted to a callable.");
    }

    /**
     * Returns a callable from a static method.
     *
     * @param array $callback The callback
     *
     * @return array The callable array
     */
    private function getCallableFromStaticMethod(array $callback)
    {
        $class = new \ReflectionClass($callback[0]);

        if (!$class->hasMethod($callback[1]) || !$class->getMethod($callback[1])->isStatic()) {
            return null;
        }

        return $callback;
    }

    /**
     * Returns a callable from a singleton object.
     *
     * @param array $callback The callback
     *
     * @return array The callable array
     */
    private function getCallableFromSingleton(array $callback)
    {
        if (null === ($callable = $this->getCallableFromStaticMethod([$callback[0], 'getInstance']))) {
            return null;
        }

        return [$callable[0]::getInstance(), $callable[1]];
    }

    /**
     * Returns a callable from a regular object.
     *
     * @param array $callback The callback
     *
     * @return array The callable array
     */
    private function getCallableFromRegularObject(array $callback)
    {
        $class = new \ReflectionClass($callback[0]);

        if (!$class->hasMethod('__construct') || !$class->getMethod('__construct')->isPublic()) {
            return null;
        }

        return [new $callback[0](), $callback[1]];
    }
}
