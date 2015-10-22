<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Event;

use Contao\CoreBundle\Event\GetImageEvent;
use Contao\CoreBundle\Test\TestCase;
use Contao\File;
use Contao\Image;

/**
 * Tests the GetImageEvent class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetImageEventTest extends TestCase
{
    /**
     * @var GetImageEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $file = new File('images/dummy.jpg');

        $this->event = new GetImageEvent(
            'images/dummy.jpg',
            200,
            150,
            'crop',
            'd/dummy-a235d.jpg',
            $file,
            'assets/images/d/dummy-a235d.jpg',
            new Image($file)
        );
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Event\GetImageEvent', $this->event);
    }

    /**
     * Tests the getters.
     */
    public function testGetters()
    {
        $this->assertNull($this->event->getReturnValue());
        $this->assertEquals('images/dummy.jpg', $this->event->getOriginalPath());
        $this->assertEquals(200, $this->event->getTargetWidth());
        $this->assertEquals(150, $this->event->getTargetHeight());
        $this->assertEquals('crop', $this->event->getResizeMode());
        $this->assertEquals('d/dummy-a235d.jpg', $this->event->getCacheName());
        $this->assertInstanceOf('Contao\File', $this->event->getFileObject());
        $this->assertEquals('assets/images/d/dummy-a235d.jpg', $this->event->getTargetPath());
        $this->assertInstanceOf('Contao\Image', $this->event->getImageObject());
    }

    /**
     * Tests the setReturnValue() method.
     */
    public function testReturnValue()
    {
        $this->event->setReturnValue('assets/images/b/dummy-f985b.jpg');
        $this->assertEquals('assets/images/b/dummy-f985b.jpg', $this->event->getReturnValue());
    }
}
