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

        $name = 'test';
        $language = 'en';
        $cacheKey = 'test-en';

        $this->event = new LoadLanguageFileEvent($name, $language, $cacheKey);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\LoadLanguageFileEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertEquals('test', $this->event->getName());
        $this->assertEquals('en', $this->event->getLanguage());
        $this->assertEquals('test-en', $this->event->getCacheKey());

        $this->event->setName('foo');
        $this->event->setLanguage('de');
        $this->event->setCacheKey('foo-de');

        $this->assertEquals('foo', $this->event->getName());
        $this->assertEquals('de', $this->event->getLanguage());
        $this->assertEquals('foo-de', $this->event->getCacheKey());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $name = 'test';
        $language = 'en';
        $cacheKey = 'test-en';

        $this->event = new LoadLanguageFileEvent($name, $language, $cacheKey);

        // Try to change the original variables
        $name = 'foo';
        $language = 'de';
        $cacheKey = 'foo-de';

        $this->assertEquals('foo', $this->event->getName());
        $this->assertEquals('de', $this->event->getLanguage());
        $this->assertEquals('foo-de', $this->event->getCacheKey());
    }
}
