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
 * Allows to execute logic when a language file is loaded.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class LoadLanguageFileEvent extends Event
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * Constructor.
     *
     * @param string $filename The file name
     * @param string $language The language
     * @param string $cacheKey The cache key
     */
    public function __construct($filename, $language, $cacheKey)
    {
        $this->filename = $filename;
        $this->language = $language;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Returns the file name.
     *
     * @return string The name
     */
    public function getFilename()
    {
        return $this->filename;
    }

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
     * Returns the cache key.
     *
     * @return string The cache key
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
}
