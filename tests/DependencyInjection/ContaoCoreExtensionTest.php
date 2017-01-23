<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\DependencyInjection;

use Contao\CoreBundle\DependencyInjection\ContaoCoreExtension;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Tests the ContaoCoreExtension class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoCoreExtensionTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $extension = new ContaoCoreExtension();

        $this->assertInstanceOf('Contao\CoreBundle\DependencyInjection\ContaoCoreExtension', $extension);
    }

    /**
     * Tests adding the bundle services to the container.
     */
    public function testLoad()
    {
        $container = new ContainerBuilder(
            new ParameterBag([
                'kernel.debug' => false,
                'kernel.root_dir' => $this->getRootDir().'/app',
            ])
        );

        $params = [
            'contao' => [
                'root_dir' => $this->getRootDir(),
                'web_dir' => $this->getRootDir().'/web',
                'encryption_key' => 'foobar',
                'localconfig' => ['foo' => 'bar'],
            ],
        ];

        $extension = new ContaoCoreExtension();
        $extension->load($params, $container);

        $this->assertTrue($container->has('contao.listener.add_to_search_index'));
        $this->assertEquals($container->getParameter('contao.web_dir_relative'), 'web');
    }

    /**
     * Tests adding the bundle services to the container with an invalid web dir.
     */
    public function testLoadInvalidWebDir()
    {
        $container = new ContainerBuilder(
            new ParameterBag([
                'kernel.debug' => false,
                'kernel.root_dir' => $this->getRootDir().'/app',
            ])
        );

        $params = [
            'contao' => [
                'root_dir' => $this->getRootDir(),
                'web_dir' => dirname($this->getRootDir()),
            ],
        ];

        $extension = new ContaoCoreExtension();

        $this->setExpectedException(InvalidArgumentException::class);

        $extension->load($params, $container);
    }

    /**
     * Tests the getAlias() method.
     */
    public function testGetAlias()
    {
        $extension = new ContaoCoreExtension();

        $this->assertEquals('contao', $extension->getAlias());
    }
}
