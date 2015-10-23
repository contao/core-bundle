<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

/**
 * Allows to execute logic when the language list is compiled.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetLanguagesEvent extends ReturnValueEvent
{
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
     * @param array $value         The array to be returned
     * @param array $languages     The languages list
     * @param array $langsNative   The native languages list
     * @param bool  $installedOnly True to return only installed languages
     */
    public function __construct(array $value, array $languages, array $langsNative, $installedOnly)
    {
        parent::__construct($value);

        $this->languages = $languages;
        $this->langsNative = $langsNative;
        $this->installedOnly = $installedOnly;
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
     * Returns the native languages list.
     *
     * @return array The native languages list
     */
    public function getLangsNative()
    {
        return $this->langsNative;
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
