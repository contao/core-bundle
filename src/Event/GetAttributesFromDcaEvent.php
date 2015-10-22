<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\DataContainer;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a data container is evaluated.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetAttributesFromDcaEvent extends Event
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @var DataContainer
     */
    private $dca;

    /**
     * Constructor.
     *
     * @param array         $attributes The attributes
     * @param DataContainer $dca        The data container object
     */
    public function __construct(array $attributes, DataContainer &$dca)
    {
        $this->attributes = $attributes;
        $this->dca = &$dca;
    }

    /**
     * Returns the attributes.
     *
     * @return array The attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets the attributes.
     *
     * @param array $attributes The attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns the data container object.
     *
     * @return DataContainer The data container object
     */
    public function getDataContainer()
    {
        return $this->dca;
    }

    /**
     * Sets the data container object.
     *
     * @param DataContainer $dca The data container object
     */
    public function setDataContainer(DataContainer $dca)
    {
        $this->dca = $dca;
    }
}
