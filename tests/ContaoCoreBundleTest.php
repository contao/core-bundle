<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Compiler\AddImagineClassPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddPackagesPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddResourcesPathsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\AddSessionBagsPass;
use Contao\CoreBundle\DependencyInjection\Compiler\DoctrineMigrationsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the ContaoCoreBundle class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class ContaoCoreBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new ContaoCoreBundle();

        $this->assertInstanceOf('Contao\CoreBundle\ContaoCoreBundle', $bundle);
    }

    /**
     * Tests the getContainerExtension() method.
     */
    public function testReturnsTheContainerExtension()
    {
        $bundle = new ContaoCoreBundle();

        $this->assertInstanceOf(
            'Contao\CoreBundle\DependencyInjection\ContaoCoreExtension',
            $bundle->getContainerExtension()
        );
    }

    /**
     * Tests the build() method.
     */
    public function testAddsTheCompilerPaths()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.root_dir', $this->getRootDir().'/app');

        $bundle = new ContaoCoreBundle();
        $bundle->build($container);

        $classes = [];
        $beforeRemovingClasses = [];

        foreach ($container->getCompilerPassConfig()->getPasses() as $pass) {
            $reflection = new \ReflectionClass($pass);
            $classes[] = $reflection->getName();
        }

        foreach ($container->getCompilerPassConfig()->getBeforeRemovingPasses() as $pass) {
            $reflection = new \ReflectionClass($pass);
            $beforeRemovingClasses[] = $reflection->getName();
        }

        $this->assertContains(AddPackagesPass::class, $classes);
        $this->assertContains(AddSessionBagsPass::class, $classes);
        $this->assertContains(AddResourcesPathsPass::class, $classes);
        $this->assertContains(AddImagineClassPass::class, $classes);
        $this->assertContains(DoctrineMigrationsPass::class, $classes);
        $this->assertContains(DoctrineMigrationsPass::class, $beforeRemovingClasses);
    }
}
