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
 * Allows to execute logic when a combined .css or .js file is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCombinedFileEvent extends Event
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var array
     */
    private $file;

    /**
     * Constructor.
     */
    public function __construct($content, $key, $mode, array $file)
    {
        $this->content = $content;
        $this->key = $key;
        $this->mode = $mode;
        $this->file = $file;
    }

    /**
     * Returns the content.
     *
     * @return string The content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content.
     *
     * @param string $content The content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns the key.
     *
     * @return string The key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the mode.
     *
     * @return string The mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Returns the file array.
     *
     * @return array The file array
     */
    public function getFile()
    {
        return $this->file;
    }
}
