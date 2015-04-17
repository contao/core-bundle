<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\EventListener\Hook;

use Contao\CoreBundle\Event\InitializeSystemEvent;
use Contao\CoreBundle\EventListener\Hook\InitializeSystemListener;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the InitializeSystemListener class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class InitializeSystemListenerTest extends TestCase
{
    /**
     * @var InitializeSystemListener
     */
    private $listener;

    /**
     * Tests the object instantiation.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->listener = new InitializeSystemListener();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\EventListener\\Hook\\InitializeSystemListener', $this->listener);
    }

    /**
     * Tests the onInitializeSystem() method.
     */
    public function testOnInitializeSystem()
    {
        $executed = false;
        $event    = new InitializeSystemEvent($this->getRootDir());

        $GLOBALS['TL_HOOKS']['initializeSystem'][] = function () use (&$executed) {
            $executed = true;
        };

        $this->listener->onInitializeSystem($event);

        $this->assertTrue($executed);

        unset($GLOBALS['TL_HOOKS']);
    }
}
