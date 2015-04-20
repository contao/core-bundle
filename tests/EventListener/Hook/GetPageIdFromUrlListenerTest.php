<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\CoreBundle\Event\ReturnValueEvent;
use Contao\CoreBundle\EventListener\Hook\GetPageIdFromUrlListener;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetPageIdFromUrlListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class GetPageIdFromUrlListenerTest extends TestCase
{
    /**
     * @var GetPageIdFromUrlListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new GetPageIdFromUrlListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\GetPageIdFromUrlListener', $this->listener);
    }

    /**
     * Tests the onGetPageIdFromUrl() method.
     */
    public function onGetPageIdFromUrl()
    {
        $event = new ReturnValueEvent('foo');

        $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = function () {
            return 'bar';
        };

        $this->listener->onGetPageIdFromUrl($event);

        $this->assertEquals('bar', $event->getValue());

        unset($GLOBALS['TL_HOOKS']);
    }
}
