<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Request;

use Contao\Input;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class is the encapsulation of the request values to keep bc with Contao 3.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 *
 * @deprecated Deprecated since Contao 4.3, to be removed in Contao 5.0. Use values from the request instead.
 *
 * @internal
 */
class ValueAdapter
{
    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * The filtered values.
     *
     * @var Request
     */
    public $filtered = [];

    /**
     * The cached values.
     *
     * @var ParameterBag[]
     */
    private $cache = [];

    /**
     * Unused $_GET parameters
     * @var array
     */
    private $unusedGet = [];

    /**
     * Create a new instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request  = $request;
        $this->filtered = $request->duplicate(
            Input::cleanKey($this->request->query->all()),
            Input::cleanKey($this->request->request->all()),
            $this->request->attributes->all(),
            Input::cleanKey($this->request->cookies->all())
        );
    }

    /**
     * Export the filtered values to the super globals.
     *
     * This is for bc with legacy code reason only.
     */
    public function exportGlobals()
    {
        // Inline reader function for reading the protected property from the parameter bag.
        // I know this approach is rather hacky but it is the only way for exporting the property
        // by reference.
        $reader = function & ($object) {
            $value = & \Closure::bind(function & () {
                if (isset($this->parameters)) {
                    return $this->parameters;
                }
                // Fall through if symfony should ever rename the internal property
                // (unlikely but we better be safe than sorry).
                trigger_error(
                    'Symfony has changed the "parameters" property name. Exporting without reference',
                    E_USER_WARNING
                );
                /** @var ParameterBag $this */
                return $this->all();
            }, $object, $object)->__invoke();

            return $value;
        };

        $_GET    = &$reader($this->filtered->query);
        $_POST   = &$reader($this->filtered->request);
        $_COOKIE = &$reader($this->filtered->cookies);
    }

    /**
     * Test if a cache value is present.
     *
     * @param string $cacheKey  The cache name.
     * @param string $valueName The value name.
     *
     * @return bool
     */
    public function hasCached($cacheKey, $valueName)
    {
        return array_key_exists($cacheKey, $this->cache) && $this->cache[$cacheKey]->has($valueName);
    }

    /**
     * Retrieve a cached value.
     *
     * @param string $cacheKey  The cache name.
     * @param string $valueName The value name.
     *
     * @return mixed|null
     */
    public function getCached($cacheKey, $valueName)
    {
        if (!array_key_exists($cacheKey, $this->cache)) {
            return null;
        }

        return $this->cache[$cacheKey]->get($valueName);
    }

    /**
     * Set a cached value and return it.
     *
     * @param string $cacheKey  The cache name.
     * @param string $valueName The value name.
     * @param mixed  $value     The value to cache.
     *
     * @return mixed
     */
    public function setCached($cacheKey, $valueName, $value)
    {
        if (!array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = new ParameterBag();
        }

        $this->cache[$cacheKey]->set($valueName, $value);

        return $value;
    }

    /**
     * Remove a cached value.
     *
     * @param string $cacheKey  The cache name.
     * @param string $valueName The value name.
     */
    public function removeCached($cacheKey, $valueName)
    {
        if (!array_key_exists($cacheKey, $this->cache)) {
            return;
        }

        $this->cache[$cacheKey]->remove($valueName);
    }

    /**
     * Clear the cache.
     *
     * @return void
     */
    public function clearCache()
    {
        $this->cache = [];
    }

    /**
     * Set the "unused" state for a GET parameter.
     *
     * @param string $name The name of the parameter.
     * @param bool   $used The state (defaults to true).
     */
    public function setUsed($name, $used = true)
    {
        if ($used) {
            unset($this->unusedGet[$name]);

            return;
        }

        $this->unusedGet[$name] = $name;
    }

    /**
     * Return whether there are unused GET parameters
     *
     * @return boolean True if there are unused GET parameters
     */
    public function hasUnusedGet()
    {
        return count($this->unusedGet) > 0;
    }

    /**
     * Return the unused GET parameters as array
     *
     * @return array The unused GET parameter array
     */
    public function getUnusedGet()
    {
        return array_keys($this->unusedGet);
    }
}
