<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when the language list is compiled.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetLanguagesEvent extends Event
{
    /**
     * @var array
     */
    private $return;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var array
     */
    private $langsNative;

    /**
     * @var bool
     */
    private $installedOnly;

    /**
     * Constructor.
     *
     * @param array $return        The array to be returned
     * @param array $languages     The languages list
     * @param array $langsNative   The native languages list
     * @param bool  $installedOnly True to return only installed languages
     */
    public function __construct(array $return, array $languages, array $langsNative, $installedOnly)
    {
        $this->return = $return;
        $this->languages = $languages;
        $this->langsNative = $langsNative;
        $this->installedOnly = $installedOnly;
    }

    /**
     * Returns the array to be returned.
     *
     * @return array The array to be returned
     */
    public function getReturnValue()
    {
        return $this->return;
    }

    /**
     * Sets the array to be returned.
     *
     * @param array $return The array to be returned
     */
    public function setReturnValue(array $return)
    {
        $this->return = $return;
    }

    /**
     * Returns the languages list.
     *
     * @return array The languages list
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Sets the languages list.
     *
     * @param array $languages The languages list
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * Returns the native languages list.
     *
     * @return array The native languages list
     */
    public function getLangsNative()
    {
        return $this->langsNative;
    }

    /**
     * Sets the native languages list.
     *
     * @param array $langsNative The native languages list
     */
    public function setLangsNative(array $langsNative)
    {
        $this->langsNative = $langsNative;
    }

    /**
     * Returns the installedOnly flag.
     *
     * @return bool The installedOnly flag
     */
    public function isInstalledOnly()
    {
        return $this->installedOnly;
    }
}
