<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Cache;

use Contao\CoreBundle\Cache\ContaoCacheClearer;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests the ContaoCacheClearer class.
 */
class ContaoCacheClearerTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->getCacheDir().'/contao');
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $clearer = new ContaoCacheClearer(new Filesystem());

        $this->assertInstanceOf('Contao\CoreBundle\Cache\ContaoCacheClearer', $clearer);
    }

    /**
     * Tests removing the cache folder.
     */
    public function testRemovesTheCacheFolder(): void
    {
        $cacheDir = $this->getCacheDir();

        $fs = new Filesystem();
        $fs->mkdir($cacheDir.'/contao/config');

        $this->assertFileExists($cacheDir.'/contao/config');

        $clearer = new ContaoCacheClearer($fs);
        $clearer->clear($cacheDir);

        $this->assertFileNotExists($cacheDir.'/contao/config');
    }
}
