<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Csrf;

use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use Contao\CoreBundle\Tests\TestCase;

/**
 * Tests the MemoryTokenStorage class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class MemoryTokenStorageTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->assertInstanceOf('Contao\CoreBundle\Csrf\MemoryTokenStorage', $memoryTokenStorage);
        $this->assertInstanceOf('Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface', $memoryTokenStorage);
    }

    public function testStoresTokens()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $memoryTokenStorage->initialize(['foo' => 'bar']);

        $this->assertTrue($memoryTokenStorage->hasToken('foo'));
        $this->assertFalse($memoryTokenStorage->hasToken('baz'));

        $memoryTokenStorage->setToken('baz', 'bar');

        $this->assertTrue($memoryTokenStorage->hasToken('baz'));
        $this->assertSame(['baz' => 'bar'], $memoryTokenStorage->getSaveTokens());
        $this->assertSame('bar', $memoryTokenStorage->getToken('foo'));
        $this->assertSame(['baz' => 'bar', 'foo' => 'bar'], $memoryTokenStorage->getSaveTokens());

        $memoryTokenStorage->removeToken('foo');
        $this->assertFalse($memoryTokenStorage->hasToken('foo'));
        $this->assertSame(['baz' => 'bar', 'foo' => null], $memoryTokenStorage->getSaveTokens());

        $memoryTokenStorage->removeToken('baz');
        $this->assertFalse($memoryTokenStorage->hasToken('baz'));
        $this->assertSame(['baz' => null, 'foo' => null], $memoryTokenStorage->getSaveTokens());
    }

    public function testGetThrowsExceptionIfNotInitialized()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->setExpectedException('LogicException');

        $memoryTokenStorage->getToken('foo');
    }

    public function testSetThrowsExceptionIfNotInitialized()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->setExpectedException('LogicException');

        $memoryTokenStorage->setToken('foo', 'bar');
    }

    public function testHasThrowsExceptionIfNotInitialized()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->setExpectedException('LogicException');

        $memoryTokenStorage->hasToken('foo');
    }

    public function testRemoveThrowsExceptionIfNotInitialized()
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->setExpectedException('LogicException');

        $memoryTokenStorage->removeToken('foo');
    }
}
