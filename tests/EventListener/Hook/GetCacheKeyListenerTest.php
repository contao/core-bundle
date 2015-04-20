<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\CoreBundle\Event\GetCacheKeyEvent;
use Contao\CoreBundle\EventListener\Hook\GetCacheKeyListener;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetCacheKeyListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class GetCacheKeyListenerTest extends TestCase
{
    /**
     * @var GetCacheKeyListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new GetCacheKeyListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\GetCacheKeyListener', $this->listener);
    }

    /**
     * Tests the onGetCacheKey() method.
     */
    public function testOnGetCacheKey()
    {
        $event = new GetCacheKeyEvent('foo');

        $GLOBALS['TL_HOOKS']['getCacheKey'][] = function () {
            return 'bar';
        };

        $this->listener->onGetCacheKey($event);

        $this->assertEquals('bar', $event->getCacheKey());

        unset($GLOBALS['TL_HOOKS']);
    }
}
