<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Request;

use Contao\CoreBundle\Request\ValueAdapter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the ValueAdapter class.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class ValueAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the internal cache works correctly.
     */
    public function testCachingWorks()
    {
        $adapter = new ValueAdapter(new Request());

        $this->assertFalse($adapter->hasCached('cacheKey', 'valueName'));
        $adapter->removeCached('cacheKey', 'valueName');
        $this->assertFalse($adapter->hasCached('cacheKey', 'valueName'));
        $this->assertNull($adapter->getCached('cacheKey', 'valueName'));
        $this->assertEquals('value', $adapter->setCached('cacheKey', 'valueName', 'value'));
        $this->assertTrue($adapter->hasCached('cacheKey', 'valueName'));
        $this->assertEquals('value', $adapter->getCached('cacheKey', 'valueName'));
        $adapter->removeCached('cacheKey', 'valueName');
        $this->assertFalse($adapter->hasCached('cacheKey', 'valueName'));

        $adapter->setCached('cacheKey', 'valueName', 'value');
        $adapter->setCached('cacheKey', 'valueName', 'value2');
        $this->assertEquals('value2', $adapter->getCached('cacheKey', 'valueName'));

        $adapter->clearCache();
        $this->assertNull($adapter->getCached('cacheKey', 'valueName'));
    }

    /**
     * Test that marking parameters as used and unused works.
     */
    public function testUsedGetParameterHandling()
    {
        $adapter = new ValueAdapter(new Request());

        $this->assertFalse($adapter->hasUnusedGet());
        $adapter->setUsed('test', false);
        $adapter->setUsed('test2', false);
        $this->assertTrue($adapter->hasUnusedGet());
        $this->assertEquals(['test', 'test2'], $adapter->getUnusedGet());
        $adapter->setUsed('test');
        $this->assertEquals(['test2'], $adapter->getUnusedGet());
    }

    /**
     * Test that exporting the globals works.
     */
    public function testExportGlobals()
    {
        $adapter = new ValueAdapter(new Request());

        $adapter->exportGlobals();

        $this->assertEmpty($_GET);
        $this->assertEmpty($_POST);
        $this->assertEmpty($_COOKIE);

        $adapter->filtered->query->set('test', 'value');
        $this->assertEquals(['test' => 'value'], $_GET);

        $adapter->filtered->request->set('test', 'value');
        $this->assertEquals(['test' => 'value'], $_POST);

        $adapter->filtered->cookies->set('test', 'value');
        $this->assertEquals(['test' => 'value'], $_COOKIE);

        $_GET['another'] = 'value';
        $this->assertEquals(['test' => 'value', 'another' => 'value'], $adapter->filtered->query->all());
    }
}
