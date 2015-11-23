<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Framework;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ContaoFramework class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @preserveGlobalState disabled
 */
class ContaoFrameworkTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $framework = new ContaoFramework();

        $this->assertInstanceOf('Contao\CoreBundle\Framework\ContaoFramework', $framework);
        $this->assertInstanceOf('Contao\CoreBundle\Framework\ContaoFrameworkInterface', $framework);
    }

    /**
     * Tests initializing the framework without an initializer.
     *
     * @runInSeparateProcess
     * @expectedException \LogicException
     */
    public function testInitializerNotSet()
    {
        $framework = $this->mockContaoFramework();
        $framework->initialize();
    }

    /**
     * Tests that the framework is not initialized twice.
     *
     * @runInSeparateProcess
     */
    public function testNotInitializedTwice()
    {
        $framework = $this->mockContaoFramework();
        $framework->setInitializer($this->mockFrameworkInitializer());

        $framework
            ->expects($this->any())
            ->method('isInitialized')
            ->willReturnOnConsecutiveCalls(false, true)
        ;

        $framework->initialize();
        $framework->initialize();
    }

    /**
     * Tests the createInstance method.
     */
    public function testCreateInstance()
    {
        $framework = new ContaoFramework();

        $class = 'Contao\CoreBundle\Test\Fixtures\Adapter\LegacyClass';
        $instance = $framework->createInstance($class, [1, 2]);

        $this->assertInstanceOf($class, $instance);
        $this->assertEquals([1, 2], $instance->constructorArgs);
    }

    /**
     * Tests the createInstance method for a singleton class.
     */
    public function testCreateInstanceSingelton()
    {
        $framework = new ContaoFramework();

        $class = 'Contao\CoreBundle\Test\Fixtures\Adapter\LegacySingletonClass';
        $instance = $framework->createInstance($class, [1, 2]);

        $this->assertInstanceOf($class, $instance);
        $this->assertEquals([1, 2], $instance->constructorArgs);
    }

    /**
     * Tests the getAdapter method.
     */
    public function testGetAdapter()
    {
        $framework = new ContaoFramework();

        $this->assertInstanceOf('Contao\CoreBundle\Framework\Adapter', $framework->getAdapter('Contao\Config'));
    }
}
