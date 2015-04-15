<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when the front end cache key is calculated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCacheKeyEvent extends Event
{
    /**
     * @var string
     */
    private $key;

    /**
     * Constructor.
     *
     * @param string $key The cache key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Sets the cache key.
     *
     * @param string $key The cache key
     */
    public function setCacheKey($key)
    {
        $this->key = $key;
    }

    /**
     * Returns the cache key.
     *
     * @return string The cache key
     */
    public function getCacheKey()
    {
        return $this->key;
    }
}
