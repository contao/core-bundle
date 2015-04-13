<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\DependencyInjection\Compiler;

use Contao\CoreBundle\Composer\VersionParser;
use Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the AddPackagesPass class.
 *
 * @author Andreas Schempp <http://github.com/aschempp>
 */
class AddPackagesPassTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $pass = new AddPackagesPass($this->getRootDir() . '/vendor/composer/installed.json', new VersionParser());

        $this->assertInstanceOf('Contao\\CoreBundle\\DependencyInjection\\Compiler\\AddPackagesPass', $pass);
    }

    /**
     * Tests processing the pass.
     */
    public function testVersions()
    {
        $pass      = new AddPackagesPass($this->getRootDir() . '/vendor/composer/installed.json', new VersionParser());
        $container = new ContainerBuilder();

        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.packages'));

        $packages = $container->getParameter('kernel.packages');

        $this->assertInternalType('array', $packages);
        $this->assertArrayHasKey('contao/test-bundle1', $packages);
        $this->assertArrayHasKey('contao/test-bundle2', $packages);
        $this->assertArrayHasKey('contao/test-bundle3', $packages);
        $this->assertArrayHasKey('contao/test-bundle4', $packages);

        $this->assertEquals('1.0.0', $packages['contao/test-bundle1']);
        $this->assertEquals('dev-develop', $packages['contao/test-bundle2']);
        $this->assertEquals('1.0.x-dev', $packages['contao/test-bundle3']);
        $this->assertEquals('invalid-dev', $packages['contao/test-bundle4']);
    }

    /**
     * Tests processing the pass.
     */
    public function testNormalized()
    {
        $pass      = new AddPackagesPass($this->getRootDir() . '/vendor/composer/installed.json', new VersionParser());
        $container = new ContainerBuilder();

        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.normalized_packages'));

        $packages = $container->getParameter('kernel.normalized_packages');

        $this->assertInternalType('array', $packages);
        $this->assertArrayHasKey('contao/test-bundle1', $packages);
        $this->assertArrayHasKey('contao/test-bundle2', $packages);
        $this->assertArrayHasKey('contao/test-bundle3', $packages);
        $this->assertArrayHasKey('contao/test-bundle4', $packages);

        $this->assertEquals('1.0.0.0', $packages['contao/test-bundle1']);
        $this->assertEquals('dev-develop', $packages['contao/test-bundle2']);
        $this->assertEquals('1.0.9999999.9999999-dev', $packages['contao/test-bundle3']);
        $this->assertEquals('dev-invalid', $packages['contao/test-bundle4']);
    }

    /**
     * Tests processing the pass without the JSON file.
     */
    public function testFileNotFound()
    {
        $pass      = new AddPackagesPass($this->getRootDir() . '/vendor/composer/invalid.json', new VersionParser());
        $container = new ContainerBuilder();

        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.packages'));

        $packages = $container->getParameter('kernel.packages');

        $this->assertInternalType('array', $packages);
        $this->assertEmpty($container->getParameter('kernel.packages'));
    }
}
