<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\DependencyInjection\Compiler;

use Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass;
use Contao\CoreBundle\Tests\TestCase;
use PackageVersions\Versions;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddPackagesPassTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $pass = new AddPackagesPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass', $pass);
    }

    public function testAddsThePackages(): void
    {
        $container = new ContainerBuilder();

        $pass = new AddPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.packages'));

        $keys = array_keys(Versions::VERSIONS);
        $packages = $container->getParameter('kernel.packages');

        $this->assertInternalType('array', $packages);
        $this->assertArrayHasKey($keys[0], $packages);
        $this->assertArrayHasKey($keys[1], $packages);
        $this->assertArrayHasKey($keys[2], $packages);
        $this->assertArrayNotHasKey('contao/test-bundle4', $packages);

        $this->assertSame($this->getVersion($keys[0]), $packages[$keys[0]]);
        $this->assertSame($this->getVersion($keys[1]), $packages[$keys[1]]);
        $this->assertSame($this->getVersion($keys[2]), $packages[$keys[2]]);
    }

    private function getVersion(string $package): string
    {
        return strtok(Versions::getVersion($package), '@');
    }
}
