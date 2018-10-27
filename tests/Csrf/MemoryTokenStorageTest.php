<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Csrf;

use Contao\CoreBundle\Csrf\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;

class MemoryTokenStorageTest extends TestCase
{
    public function testStoresAndRemovesTokens(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();
        $memoryTokenStorage->initialize(['foo' => 'bar']);

        $this->assertTrue($memoryTokenStorage->hasToken('foo'));
        $this->assertFalse($memoryTokenStorage->hasToken('baz'));

        $memoryTokenStorage->setToken('baz', 'bar');

        $this->assertTrue($memoryTokenStorage->hasToken('baz'));
        $this->assertSame(['baz' => 'bar'], $memoryTokenStorage->getUsedTokens());
        $this->assertSame('bar', $memoryTokenStorage->getToken('foo'));
        $this->assertSame(['foo' => 'bar', 'baz' => 'bar'], $memoryTokenStorage->getUsedTokens());

        $memoryTokenStorage->removeToken('foo');

        $this->assertFalse($memoryTokenStorage->hasToken('foo'));
        $this->assertSame(['foo' => null, 'baz' => 'bar'], $memoryTokenStorage->getUsedTokens());

        $memoryTokenStorage->removeToken('baz');

        $this->assertFalse($memoryTokenStorage->hasToken('baz'));
        $this->assertSame(['foo' => null, 'baz' => null], $memoryTokenStorage->getUsedTokens());
    }

    public function testDoesNotReturnUsedTokensIfNotInitialized(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->assertSame([], $memoryTokenStorage->getUsedTokens());
    }

    public function testFailsIfATokenDoesNotExist(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();
        $memoryTokenStorage->initialize(['foo' => 'bar']);

        $this->expectException(TokenNotFoundException::class);

        $memoryTokenStorage->getToken('bar');
    }

    public function testFailsToReturnATokenIfNotInitialized(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->expectException('LogicException');

        $memoryTokenStorage->getToken('foo');
    }

    public function testFailsToStoreATokenIfNotInitialized(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->expectException('LogicException');

        $memoryTokenStorage->setToken('foo', 'bar');
    }

    public function testFailsToCheckForATokenIfNotInitialized(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->expectException('LogicException');

        $memoryTokenStorage->hasToken('foo');
    }

    public function testFailsToRemoveATokenIfNotInitialized(): void
    {
        $memoryTokenStorage = new MemoryTokenStorage();

        $this->expectException('LogicException');

        $memoryTokenStorage->removeToken('foo');
    }
}
