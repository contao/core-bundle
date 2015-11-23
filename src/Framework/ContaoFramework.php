<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Framework;

/**
 * Contao 3 framework service.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @internal Do not instantiate this class in your code. Use the "contao.framework" service instead.
 */
class ContaoFramework implements ContaoFrameworkInterface
{
    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var FrameworkInitializer
     */
    private $initializer;

    /**
     * @var array
     */
    private $adapterCache = [];

    /**
     * Sets the framework initializer.
     *
     * @param FrameworkInitializer|null $initializer The framework initializer
     */
    public function setInitializer(FrameworkInitializer $initializer = null)
    {
        $this->initializer = $initializer;
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialized()
    {
        return self::$initialized;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException If the container is not set
     */
    public function initialize()
    {
        if ($this->isInitialized()) {
            return;
        }

        // Set before calling any methods to prevent recursion
        self::$initialized = true;

        if (null === $this->initializer) {
            throw new \LogicException('The framwork initializer has not been set.');
        }

        $this->initializer->setFramework($this);
        $this->initializer->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($class, $args = [])
    {
        if (in_array('getInstance', get_class_methods($class))) {
            return call_user_func_array([$class, 'getInstance'], $args);
        }

        $reflection = new \ReflectionClass($class);

        return $reflection->newInstanceArgs($args);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter($class)
    {
        if (!isset($this->adapterCache[$class])) {
            $this->adapterCache[$class] = new Adapter($class);
        }

        return $this->adapterCache[$class];
    }
}
