<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetSearchablePagesEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the GetSearchablePagesEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetSearchablePagesEventTest extends TestCase
{
    /**
     * @var GetSearchablePagesEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $pages = [];
        $rootId = 1;
        $language = 'en';

        $this->event = new GetSearchablePagesEvent($pages, $rootId, $language);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetSearchablePagesEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals([], $this->event->getPages());
        $this->assertEquals(1, $this->event->getRootId());
        $this->assertEquals('en', $this->event->getLanguage());

        $this->event->setPages([2]);
        $this->event->setRootId(2);
        $this->event->setLanguage('de');

        $this->assertEquals([2], $this->event->getPages());
        $this->assertEquals(2, $this->event->getRootId());
        $this->assertEquals('de', $this->event->getLanguage());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $pages = [];
        $rootId = 2;
        $language = 'en';

        $this->event = new GetSearchablePagesEvent($pages, $rootId, $language);

        // Try to change the original variables
        $pages = [2, 3];
        $rootId = 3;
        $language = 'fr';

        $this->assertEquals([], $this->event->getPages());
        $this->assertEquals(3, $this->event->getRootId());
        $this->assertEquals('fr', $this->event->getLanguage());
    }
}
