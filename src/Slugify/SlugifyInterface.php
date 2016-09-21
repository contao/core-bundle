<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Slugify;

/**
 * Returns an URL safe version of a string.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 */
interface SlugifyInterface
{
    /**
     * Returns an URL safe version of a string.
     *
     * @param string      $string
     * @param string|null $language
     *
     * @return string
     */
    public function slugify($string, $language = null);

    /**
     * Returns the Slugify ruleset for a language.
     *
     * @param string $language
     *
     * @return string
     */
    public function getRulesetForLanguage($language);
}
