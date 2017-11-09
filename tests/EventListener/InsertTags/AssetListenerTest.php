<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener\InsertTags;

use Contao\CoreBundle\EventListener\InsertTags\AssetListener;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Asset\Packages;

class AssetListenerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $listener = new AssetListener(new Packages());

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\InsertTags\AssetListener', $listener);
    }

    public function testReplacesAssetInsertTagWithPackageName()
    {
        $packages = $this->createMock(Packages::class);
        $listener = new AssetListener($packages);

        $packages
            ->expects($this->once())
            ->method('getUrl')
            ->with('foo/bar', 'package')
            ->willReturnArgument(0)
        ;

        $value = $listener->onReplaceInsertTags('asset::foo/bar::package');

        $this->assertSame('foo/bar', $value);
    }

    public function testReplacesAssetInsertTagWithoutPackageName()
    {
        $packages = $this->createMock(Packages::class);
        $listener = new AssetListener($packages);

        $packages
            ->expects($this->once())
            ->method('getUrl')
            ->with('foo/bar', null)
            ->willReturnArgument(0)
        ;

        $value = $listener->onReplaceInsertTags('asset::foo/bar');

        $this->assertSame('foo/bar', $value);
    }

    public function testIgnoresOtherInsertTags()
    {
        $packages = $this->createMock(Packages::class);
        $listener = new AssetListener($packages);

        $packages
            ->expects($this->never())
            ->method('getUrl')
        ;

        $this->assertFalse($listener->onReplaceInsertTags('env::pageTitle'));
    }
}
