<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\ContaoManager\Plugin;
use Contao\ManagerBundle\ContaoManagerBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\DelegatingParser;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Knp\Bundle\TimeBundle\KnpTimeBundle;
use Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Terminal42\HeaderReplay\HeaderReplayBundle;

/**
 * Tests the Plugin class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PluginTest extends TestCase
{
    /**
     * Tests returning the bundles.
     */
    public function testReturnsTheBundles()
    {
        $plugin = new Plugin();

        /** @var BundleConfig[] $bundles */
        $bundles = $plugin->getBundles(new DelegatingParser());

        $this->assertCount(4, $bundles);

        $this->assertSame(KnpMenuBundle::class, $bundles[0]->getName());
        $this->assertSame([], $bundles[1]->getReplace());
        $this->assertSame([], $bundles[1]->getLoadAfter());

        $this->assertSame(KnpTimeBundle::class, $bundles[1]->getName());
        $this->assertSame([], $bundles[2]->getReplace());
        $this->assertSame([], $bundles[2]->getLoadAfter());

        $this->assertSame(HeaderReplayBundle::class, $bundles[2]->getName());
        $this->assertSame([], $bundles[0]->getReplace());
        $this->assertSame([], $bundles[0]->getLoadAfter());

        $this->assertSame(ContaoCoreBundle::class, $bundles[3]->getName());
        $this->assertSame(['core'], $bundles[3]->getReplace());

        $loadAfter = $bundles[3]->getLoadAfter();
        sort($loadAfter);

        $this->assertSame(
            [
                ContaoManagerBundle::class,
                DoctrineBundle::class,
                DoctrineCacheBundle::class,
                KnpMenuBundle::class,
                KnpTimeBundle::class,
                LexikMaintenanceBundle::class,
                FrameworkBundle::class,
                MonologBundle::class,
                SecurityBundle::class,
                SwiftmailerBundle::class,
                TwigBundle::class,
            ],
            $loadAfter
        );
    }

    /**
     * Tests returning the route collection.
     */
    public function testReturnsTheRouteCollection()
    {
        $loader = $this->createMock(LoaderInterface::class);

        $loader
            ->expects($this->once())
            ->method('load')
        ;

        $resolver = $this->createMock(LoaderResolverInterface::class);

        $resolver
            ->method('resolve')
            ->willReturn($loader)
        ;

        $plugin = new Plugin();
        $plugin->getRouteCollection($resolver, $this->createMock(KernelInterface::class));
    }
}
