<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\DependencyInjection\Compiler;

use Contao\CoreBundle\DependencyInjection\Compiler\ContaoRootDirPass;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the ContaoRootDirPass class.
 *
 * @author Leo Feyer <http://github.com/leofeyer>
 */
class ContaoRootDirPassTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $pass = new ContaoRootDirPass();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\Compiler\ContaoRootDirPass', $pass);
    }

    /**
     * Tests the process() method.
     */
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->setParameter('contao.root_dir', $this->getRootDir().'/..');

        $pass = new ContaoRootDirPass();
        $pass->process($container);

        $this->assertEquals(dirname($this->getRootDir()), $container->getParameter('contao.root_dir'));
    }
}
