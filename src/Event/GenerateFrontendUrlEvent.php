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
 * Allows to execute logic when a front end URL is generated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GenerateFrontendUrlEvent extends Event
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $row;

    /**
     * @var string
     */
    private $params;

    /**
     * Constructor.
     *
     * @param string $url    The URL
     * @param array  $row    The row
     * @param string $params The parameters
     */
    public function __construct($url, array &$row, &$params)
    {
        $this->url    = $url;
        $this->row    = &$row;
        $this->params = &$params;
    }

    /**
     * Returns the url.
     *
     * @return string The url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url.
     *
     * @param string $url The url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the row.
     *
     * @return array The row
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Sets the row.
     *
     * @param array $row The row
     */
    public function setRow(array $row)
    {
        $this->row = $row;
    }

    /**
     * Returns the params.
     *
     * @return string The params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the params.
     *
     * @param string $params The params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}
