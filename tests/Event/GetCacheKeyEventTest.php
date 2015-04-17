<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetCacheKeyEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetCacheKeyEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetCacheKeyEventTest extends TestCase
{
    /**
     * @var GetCacheKeyEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new GetCacheKeyEvent('foo');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GetCacheKeyEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $cacheKey = 'bar';

        $this->event->setCacheKey($cacheKey);

        $this->assertEquals($cacheKey, $this->event->getCacheKey());
    }
}
