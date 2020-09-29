<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Fixtures\Adapter;

class LegacySingletonClass
{
    public $constructorArgs = [];

    private function __construct($arg1 = null, $arg2 = null)
    {
        $this->constructorArgs = [$arg1, $arg2];
    }

    public static function getInstance($arg1 = null, $arg2 = null)
    {
        return new static($arg1, $arg2);
    }
}
