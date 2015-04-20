<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\BackendUser;
use Contao\CoreBundle\EventListener\Hook\AbstractHookListener;
use Contao\CoreBundle\Test\TestCase;
use Contao\LayoutModel;

/**
 * Tests the AbstractHookListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class AbstractHookListenerTest extends TestCase
{
    /**
     * @var AbstractHookListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = $this->getMockForAbstractClass(
            'Contao\\CoreBundle\\EventListener\\Hook\\AbstractHookListener',
            [],
            '',
            false,
            true,
            true,
            ['getHookName']
        );

        $this->listener
            ->expects($this->any())
            ->method('getHookName')
            ->willReturn('test')
        ;
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\AbstractHookListener', $this->listener);
    }

    /**
     * Tests the getCallbacks() method.
     */
    public function testGetCallbacks()
    {
        $reflection = new \ReflectionClass($this->listener);
        $method     = $reflection->getMethod('getCallbacks');

        $method->setAccessible(true);

        $this->assertEquals([], $method->invoke($this->listener));

        $GLOBALS['TL_HOOKS']['test'][] = ['Foo', 'bar'];

        $this->assertEquals([['Foo', 'bar']], $method->invoke($this->listener));

        unset($GLOBALS['TL_HOOKS']);
    }

    /**
     * Tests the getCallable() method.
     */
    public function testGetCallable()
    {
        $reflection = new \ReflectionClass($this->listener);
        $method     = $reflection->getMethod('getCallable');

        $method->setAccessible(true);

        $this->assertEquals(['Contao\\System', 'getReferer'], $method->invokeArgs($this->listener, [['Contao\\System', 'getReferer']]));

        $callable = function() {};

        $this->assertEquals($callable, $method->invokeArgs($this->listener, [$callable]));
    }

    /**
     * Tests the getCallable() method with an invalid callback.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetCallableWithInvalidCallback()
    {
        $reflection = new \ReflectionClass($this->listener);
        $method     = $reflection->getMethod('getCallable');

        $method->setAccessible(true);
        $method->invokeArgs($this->listener, [null]);
    }

    /**
     * Tests the getCallableFromArray() method.
     */
    public function testGetCallableFromArray()
    {
        $reflection = new \ReflectionClass($this->listener);
        $method     = $reflection->getMethod('getCallableFromArray');

        $method->setAccessible(true);

        $this->assertEquals(['Contao\\System', 'getReferer'], $method->invokeArgs($this->listener, [['Contao\\System', 'getReferer']]));
        $this->assertEquals([BackendUser::getInstance(), 'getInstance'], $method->invokeArgs($this->listener, [['Contao\\BackendUser', 'get']]));
        $this->assertEquals([new LayoutModel(), 'get'], $method->invokeArgs($this->listener, [['Contao\\LayoutModel', 'get']]));
    }

    /**
     * Tests the getCallableFromArray() method with an invalid callback.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetCallableFromArrayWithInvalidCallback()
    {
        $reflection = new \ReflectionClass($this->listener);
        $method     = $reflection->getMethod('getCallableFromArray');

        $method->setAccessible(true);
        $method->invokeArgs($this->listener, [['Contao\\Template', 'get']]);
    }
}
