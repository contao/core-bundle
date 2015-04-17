<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\InitializeSystemEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the InitializeSystemEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class InitializeSystemEventTest extends TestCase
{
    /**
     * @var InitializeSystemEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new InitializeSystemEvent($this->getRootDir());
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\InitializeSystemEvent', $this->event);
    }

    /**
     * Tests the getRootDir() method.
     */
    public function testGetRootDir()
    {
        $this->assertEquals($this->getRootDir(), $this->event->getRootDir());
    }
}
