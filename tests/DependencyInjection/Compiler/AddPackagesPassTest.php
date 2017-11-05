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
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddPackagesPassTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $pass = new AddPackagesPass($this->getFixturesDir().'/vendor/composer/installed.json');

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass', $pass);
    }

    public function testAddsThePackages(): void
    {
        $container = new ContainerBuilder();

        $pass = new AddPackagesPass($this->getFixturesDir().'/vendor/composer/installed.json');
        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.packages'));

        $packages = $container->getParameter('kernel.packages');

        $this->assertInternalType('array', $packages);
        $this->assertArrayHasKey('contao/test-bundle1', $packages);
        $this->assertArrayHasKey('contao/test-bundle2', $packages);
        $this->assertArrayHasKey('contao/test-bundle3', $packages);
        $this->assertArrayNotHasKey('contao/test-bundle4', $packages);

        $this->assertSame('1.0.0', $packages['contao/test-bundle1']);
        $this->assertSame('dev-develop', $packages['contao/test-bundle2']);
        $this->assertSame('1.1.x-dev', $packages['contao/test-bundle3']);
    }

    public function testAddsAnEmptyArrayIfThereIsNoJsonFile(): void
    {
        $container = new ContainerBuilder();

        $pass = new AddPackagesPass($this->getFixturesDir().'/vendor/composer/invalid.json');
        $pass->process($container);

        $this->assertTrue($container->hasParameter('kernel.packages'));

        $packages = $container->getParameter('kernel.packages');

        $this->assertInternalType('array', $packages);
        $this->assertEmpty($container->getParameter('kernel.packages'));
    }
}
