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
 * Allows to execute logic when the country list is compiled.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCountriesEvent extends Event
{
    /**
     * @var array
     */
    private $return;

    /**
     * @var array
     */
    private $countries;

    /**
     * Constructor.
     *
     * @param array $return    The array to be returned
     * @param array $countries The countries list
     */
    public function __construct(array &$return, array &$countries)
    {
        $this->return = &$return;
        $this->countries = &$countries;
    }

    /**
     * Returns the array to be returned.
     *
     * @return array The array to be returned
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Sets the array to be returned.
     *
     * @param array $return The array to be returned
     */
    public function setReturn(array $return)
    {
        $this->return = $return;
    }

    /**
     * Returns the countries list.
     *
     * @return array The countries list
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Sets the countries list.
     *
     * @param array $countries The countries list
     */
    public function setCountries(array $countries)
    {
        $this->countries = $countries;
    }
}
