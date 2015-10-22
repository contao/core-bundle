<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\LoadLanguageFileEvent;
use Contao\CoreBundle\Test\TestCase;

/**
 * Tests the LoadLanguageFileEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class LoadLanguageFileEventTest extends TestCase
{
    /**
     * @var LoadLanguageFileEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->event = new LoadLanguageFileEvent('test', 'en', 'test-en');
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\LoadLanguageFileEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertEquals('test', $this->event->getName());
        $this->assertEquals('en', $this->event->getLanguage());
        $this->assertEquals('test-en', $this->event->getCacheKey());
    }
}
