<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Test\Autoload;

use Contao\Bundle\CoreBundle\Autoload\Config;
use Contao\Bundle\CoreBundle\Autoload\ConfigCollection;

class ConfigCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfAndImplements()
    {
        $collection = new ConfigCollection();

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\ConfigCollection', $collection);
        $this->assertInstanceOf('\Traversable', $collection);
    }

    public function testAddAndAll()
    {
        $collection = new ConfigCollection();
        $config = new Config();

        $collection->add($config);

        $this->assertSame([$config], $collection->all());
    }

    public function testEmptyAll()
    {
        $collection = new ConfigCollection();

        $this->assertSame([], $collection->all());
    }

    public function testIsTraversable()
    {
        $collection = new ConfigCollection();

        $this->assertInstanceOf('\Traversable', $collection);
    }
}
 