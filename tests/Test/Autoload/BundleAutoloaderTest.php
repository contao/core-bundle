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

use Contao\Bundle\CoreBundle\Autoload\BundleAutoloader;

class BundleAutoloaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $bundleLoader = new BundleAutoloader('rootDir', 'env');

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\BundleAutoloader', $bundleLoader);
    }

    public function testGetRootDir()
    {
        $bundleLoader = new BundleAutoloader('rootDir', 'env');

        $this->assertSame('rootDir', $bundleLoader->getRootDir());
    }

    public function testGetEnvironment()
    {
        $bundleLoader = new BundleAutoloader('rootDir', 'env');

        $this->assertSame('env', $bundleLoader->getEnvironment());
    }
}
 