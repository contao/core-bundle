<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Adapter;

/**
 * Provides an adapter interface for the Contao Config class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ConfigAdapterInterface
{
    /**
     * Return the current object instance (Singleton)
     *
     * @return \Config The object instance
     */
    public function instantiate();

    /**
     * Save the local configuration file
     */
    public function save();

    /**
     * Return true if the installation is complete
     *
     * @return boolean True if the installation is complete
     */
    public function isComplete();

    /**
     * Return all active modules as array
     *
     * @return array An array of active modules
     *
     * @deprecated Use ModuleLoader::getActive() instead
     */
    public function getActiveModules();

    /**
     * Add a configuration variable to the local configuration file
     *
     * @param string $strKey   The full variable name
     * @param mixed  $varValue The configuration value
     */
    public function add($strKey, $varValue);

    /**
     * Alias for Config::add()
     *
     * @param string $strKey   The full variable name
     * @param mixed  $varValue The configuration value
     */
    public function update($strKey, $varValue);

    /**
     * Remove a configuration variable
     *
     * @param string $strKey The full variable name
     */
    public function delete($strKey);

    /**
     * Return a configuration value
     *
     * @param string $strKey The short key (e.g. "displayErrors")
     *
     * @return mixed|null The configuration value
     */
    public function get($strKey);

    /**
     * Temporarily set a configuration value
     *
     * @param string $strKey   The short key (e.g. "displayErrors")
     * @param string $varValue The configuration value
     */
    public function set($strKey, $varValue);

    /**
     * Permanently set a configuration value
     *
     * @param string $strKey   The short key or full variable name
     * @param mixed  $varValue The configuration value
     */
    public function persist($strKey, $varValue);

    /**
     * Permanently remove a configuration value
     *
     * @param string $strKey The short key or full variable name
     */
    public function remove($strKey);

    /**
     * Preload the default and local configuration
     */
    public function preload();
}
