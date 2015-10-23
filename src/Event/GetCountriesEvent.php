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
 * Allows to execute logic when the country list is compiled.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCountriesEvent extends ReturnValueEvent
{
    /**
     * @var array
     */
    private $countries;

    /**
     * Constructor.
     *
     * @param array $value     The array to be returned
     * @param array $countries The countries list
     */
    public function __construct(array $value, array $countries)
    {
        parent::__construct($value);

        $this->countries = $countries;
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
}
