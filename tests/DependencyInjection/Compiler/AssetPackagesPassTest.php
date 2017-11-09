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

use Contao\CoreBundle\DependencyInjection\Compiler\AssetPackagesPass;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class AssetPackagesPassTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $pass = new AssetPackagesPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\AssetPackagesPass', $pass);
    }

    public function testAbortsIfAssetServiceDoesNotExist()
    {
        $pass = new AssetPackagesPass();

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

        $pass->process($container);
    }

    public function testIgnoresBundlesWithoutPublicDir()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $bundle = $this->mockBundle('FooBarBundle', false);
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $container->setDefinition('assets.packages', $definition);
        $container->set('kernel', $kernel);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle])
        ;

        $bundle
            ->expects($this->never())
            ->method('getContainerExtension')
        ;

        $pass->process($container);

        $this->assertEmpty($container->getDefinition('assets.packages')->getMethodCalls());
    }

    public function testUsesBundleExtensionAliasForPackageName()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $bundle = $this->mockBundle('BarBundle');
        $extension = $this->createMock(ExtensionInterface::class);
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $container->setDefinition('assets.packages', $definition);
        $container->set('kernel', $kernel);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle])
        ;

        $bundle
            ->expects($this->once())
            ->method('getContainerExtension')
            ->willReturn($extension)
        ;

        $extension
            ->expects($this->once())
            ->method('getAlias')
            ->willReturn('foo_bar')
        ;

        $pass->process($container);
        $calls = $container->getDefinition('assets.packages')->getMethodCalls();

        $this->assertCount(1, $calls);
        $this->assertSame('addPackage', $calls[0][0]);
        $this->assertSame('foo_bar', $calls[0][1][0]);
        $this->assertTrue($container->hasDefinition('assets._package_foo_bar'));

        $service = $container->getDefinition('assets._package_foo_bar');
        $this->assertSame('bundles/bar', $service->getArgument(0));
        $this->assertSame('assets.empty_version_strategy', (string) $service->getArgument(1));
        $this->assertSame('contao.assets.plugins_context', (string) $service->getArgument(2));
    }

    public function testFallsBackToBundleForPackageName()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $bundle = $this->mockBundle('FooBarBundle');
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $container->setDefinition('assets.packages', $definition);
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->set('kernel', $kernel);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle])
        ;

        $bundle
            ->expects($this->once())
            ->method('getContainerExtension')
            ->willReturn(null)
        ;

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

    public function testPrefersDefaultVersionStrategyForBundles()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $bundle = $this->mockBundle('BarBundle');
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $container->setDefinition('assets.packages', $definition);
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->setDefinition('assets._version_default', new Definition(StaticVersionStrategy::class));
        $container->set('kernel', $kernel);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle])
        ;

        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_bar'));

        $service = $container->getDefinition('assets._package_bar');
        $this->assertSame('assets._version_default', (string) $service->getArgument(1));
    }

    public function testSupportsBundleWithWrongSuffix()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $bundle = $this->mockBundle('FooBarPackage');
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $container->setDefinition('assets.packages', $definition);
        $container->setDefinition('assets.empty_version_strategy', new Definition(EmptyVersionStrategy::class));
        $container->set('kernel', $kernel);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([$bundle])
        ;

        $pass->process($container);

        $this->assertTrue($container->hasDefinition('assets._package_foo_bar_package'));

        $service = $container->getDefinition('assets._package_foo_bar_package');
        $this->assertSame('bundles/foobarpackage', $service->getArgument(0));
    }

    public function testRegistersComponents()
    {
        $pass = new AssetPackagesPass();
        $kernel = $this->createMock(Kernel::class);
        $container = $this->mockContainer();
        $definition = new Definition(Packages::class);

        $composer = [
            'contao-components/foo' => '1.2.3',
            'vendor/bar' => '3.2.1',
        ];

        $container->setDefinition('assets.packages', $definition);
        $container->set('kernel', $kernel);
        $container->setParameter('kernel.packages', $composer);

        $kernel
            ->expects($this->once())
            ->method('getBundles')
            ->willReturn([])
        ;

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

    private function mockBundle($name, $tempDir = true)
    {
        $builder = $this->getMockBuilder(Bundle::class);

        $builder
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMockClassName($name)
        ;

        /** @var \PHPUnit_Framework_MockObject_MockObject|Bundle $mock */
        $mock = $builder->getMock();

        $mock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn(static::getTempDir().'/'.$mock->getName())
        ;

        if ($tempDir) {
            (new Filesystem())->mkdir(static::getTempDir().'/'.$mock->getName().'/Resources/public');
        }

        return $mock;
    }
}
