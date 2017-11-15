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

use Contao\CoreBundle\DependencyInjection\Compiler\AddAssetsPackagesPass;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Filesystem\Filesystem;

class AddAssetsPackagesPassTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $pass = new AddAssetsPackagesPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\AddAssetsPackagesPass', $pass);
    }

    public function testAbortsIfTheAssetsPackagesServiceDoesNotExist(): void
    {
        $container = $this->createMock(ContainerBuilder::class);

        $container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('assets.packages')
            ->willReturn(false)
        ;

        $container
            ->expects($this->never())
            ->method('getDefinition')
        ;

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);
    }

    public function testIgnoresBundlesWithoutPublicFolder(): void
    {
        $container = $this->mockContainer();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertEmpty($container->getDefinition('assets.packages')->getMethodCalls());
    }

    public function testUsesTheBundleNameAsPackageName(): void
    {
        $bundlePath = static::getTempDir().'/FooBarBundle';

        (new Filesystem())->mkdir($bundlePath.'/Resources/public');

        $container = $this->mockContainer();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->setParameter('kernel.bundles', ['FooBarBundle' => 'Foo\Bar\FooBarBundle']);
        $container->setParameter('kernel.bundles_metadata', ['FooBarBundle' => ['path' => $bundlePath]]);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $calls = $container->getDefinition('assets.packages')->getMethodCalls();

        $this->assertCount(1, $calls);
        $this->assertSame('addPackage', $calls[0][0]);
        $this->assertSame('foo_bar', $calls[0][1][0]);
        $this->assertTrue($container->hasDefinition('assets._package_foo_bar'));

        $service = $container->getDefinition('assets._package_foo_bar');
        $this->assertSame('bundles/foobar', $service->getArgument(0));
        $this->assertSame('assets.empty_version_strategy', (string) $service->getArgument(1));
        $this->assertSame('contao.assets.plugins_context', (string) $service->getArgument(2));
    }

    public function testUsesTheDefaultVersionStrategyForBundles(): void
    {
        $bundlePath = static::getTempDir().'/FooBarBundle';

        (new Filesystem())->mkdir($bundlePath.'/Resources/public');

        $container = $this->mockContainer();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->setDefinition('assets._version_default', new Definition(StaticVersionStrategy::class));
        $container->setParameter('kernel.bundles', ['FooBarBundle' => 'Foo\Bar\FooBarBundle']);
        $container->setParameter('kernel.bundles_metadata', ['FooBarBundle' => ['path' => $bundlePath]]);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_foo_bar'));

        $service = $container->getDefinition('assets._package_foo_bar');

        $this->assertSame('assets._version_default', (string) $service->getArgument(1));
    }

    public function testSupportsBundlesWithWrongSuffix(): void
    {
        $bundlePath = static::getTempDir().'/FooBarPackage';

        (new Filesystem())->mkdir($bundlePath.'/Resources/public');

        $container = $this->mockContainer();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->setParameter('kernel.bundles', ['FooBarPackage' => 'Foo\Bar\FooBarPackage']);
        $container->setParameter('kernel.bundles_metadata', ['FooBarPackage' => ['path' => $bundlePath]]);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_foo_bar_package'));

        $service = $container->getDefinition('assets._package_foo_bar_package');

        $this->assertSame('bundles/foobarpackage', $service->getArgument(0));
    }

    public function testRegistersTheContaoComponents(): void
    {
        $composer = [
            'contao-components/foo' => '1.2.3',
            'vendor/bar' => '3.2.1',
        ];

        $container = $this->mockContainer();
        $container->setDefinition('assets.packages', new Definition(Packages::class));
        $container->setParameter('kernel.bundles', []);
        $container->setParameter('kernel.bundles_metadata', []);
        $container->setParameter('kernel.packages', $composer);

        $pass = new AddAssetsPackagesPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_contao-components/foo'));
        $this->assertTrue($container->hasDefinition('assets._version_contao-components/foo'));
        $this->assertFalse($container->hasDefinition('assets._package_vendor/bar'));
        $this->assertFalse($container->hasDefinition('assets._version_vendor/bar'));

        $service = $container->getDefinition('assets._package_contao-components/foo');

        $this->assertSame('assets._version_contao-components/foo', (string) $service->getArgument(1));

        $version = $container->getDefinition('assets._version_contao-components/foo');

        $this->assertSame('1.2.3', $version->getArgument(0));
    }
}
