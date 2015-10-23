<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\ReadValueEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the ReadValueEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReadValueEventTest extends TestCase
{
    /**
     * @var ReadValueEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new ReadValueEvent('foo');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\ReadValueEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetter()
    {
        $this->assertEquals('foo', $this->event->getValue());
    }
}
