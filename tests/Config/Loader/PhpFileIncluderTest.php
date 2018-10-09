<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Config\Loader;

use Contao\CoreBundle\Config\Loader\PhpFileIncluder;
use Contao\CoreBundle\Tests\TestCase;

/**
 * Tests the PhpFileIncluderTest class.
 *
 * @author Mike vom Scheidt <https://github.com/mvscheidt>
 */
class PhpFileIncluderTest extends TestCase
{
    /**
     * @var PhpFileIncluder
     */
    private $loader;

    /**
     * Creates the PhpFileIncluder object.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PhpFileIncluder();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Config\Loader\PhpFileIncluder', $this->loader);
    }

    /**
     * Tests that only PHP files are supported.
     */
    public function testSupportsPhpFiles()
    {
        $this->assertTrue(
            $this->loader->supports(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/tl_test.php'
            )
        );

        $this->assertFalse(
            $this->loader->supports(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf'
            )
        );
    }

    /**
     * Tests loading a PHP file.
     */
    public function testIncludesPhpFiles()
    {
        $this->loader->load($this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/tl_test.php');

        $this->assertArrayHasKey('TL_TEST', $GLOBALS);
        $this->assertEquals(true, $GLOBALS['TL_TEST']);
    }
}
