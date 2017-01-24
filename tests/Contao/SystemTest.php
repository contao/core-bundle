<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Contao;

use Contao\CoreBundle\Test\TestCase;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the System class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @group legacy
 */
class SystemTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        define('TL_ERROR', 'ERROR');
        define('TL_ROOT', $this->getRootDir());
    }

    /**
     * Tests the stripRootDir() method.
     */
    public function testStripRootDir()
    {
        $this->assertEquals('', System::stripRootDir($this->getRootDir().'/'));
        $this->assertEquals('', System::stripRootDir($this->getRootDir().'\\'));
        $this->assertEquals('foo', System::stripRootDir($this->getRootDir().'/foo'));
        $this->assertEquals('foo', System::stripRootDir($this->getRootDir().'\\foo'));
        $this->assertEquals('foo/', System::stripRootDir($this->getRootDir().'/foo/'));
        $this->assertEquals('foo\\', System::stripRootDir($this->getRootDir().'\foo\\'));
        $this->assertEquals('foo/bar', System::stripRootDir($this->getRootDir().'/foo/bar'));
        $this->assertEquals('foo\bar', System::stripRootDir($this->getRootDir().'\foo\bar'));
    }

    /**
     * Tests the stripRootDir() method.
     */
    public function testStripRootDirDifferentPath()
    {
        $this->setExpectedException('InvalidArgumentException');

        System::stripRootDir('/foo');
    }

    /**
     * Tests the stripRootDir() method.
     */
    public function testStripRootDirParentPath()
    {
        $this->setExpectedException('InvalidArgumentException');

        System::stripRootDir(dirname($this->getRootDir()).'/');
    }

    /**
     * Tests the stripRootDir() method.
     */
    public function testStripRootDirSuffix()
    {
        $this->setExpectedException('InvalidArgumentException');

        System::stripRootDir($this->getRootDir().'foo/');
    }

    /**
     * Tests the stripRootDir() method.
     */
    public function testStripRootDirNoSlash()
    {
        $this->setExpectedException('InvalidArgumentException');

        System::stripRootDir($this->getRootDir());
    }
}
