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
 * Allows to execute logic when a front end page is indexed.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class IndexPageEvent extends Event
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $set;

    /**
     * Constructor.
     *
     * @param string $content The content
     * @param array  $data    The data array
     * @param array  $set     The set array
     */
    public function __construct(&$content, array &$data, array &$set)
    {
        $this->content = &$content;
        $this->data    = &$data;
        $this->set     = &$set;
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
     * Returns the data.
     *
     * @return array The data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data.
     *
     * @param array $data The data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the set array.
     *
     * @return array The set array
     */
    public function getSet()
    {
        return $this->set;
    }

    /**
     * Sets the set array.
     *
     * @param array $set The set array
     */
    public function setSet(array $set)
    {
        $this->set = $set;
    }
}
