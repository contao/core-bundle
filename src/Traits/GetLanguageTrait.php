<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Traits;

/**
 * Adds a $language property with getters and setters.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait GetLanguageTrait
{
    /**
     * @var string
     */
    private $language;

    /**
     * Returns the language.
     *
     * @return string The language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the language.
     *
     * @param string $language The language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
}
