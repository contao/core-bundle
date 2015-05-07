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

        $origPath     = 'orig/test.jpg';
        $targetWidth  = 200;
        $targetHeight = 150;
        $resizeMode   = 'crop';
        $cacheName    = 'd/test-a235d.jpg';
        $fileObj      = new File('orig/test.jpg');
        $targetPath   = 'assets/images/d/test-a235d.jpg';
        $imageObj     = new Image($fileObj);

        $this->event = new GetImageEvent(
            $origPath,
            $targetWidth,
            $targetHeight,
            $resizeMode,
            $cacheName,
            $fileObj,
            $targetPath,
            $imageObj
        );
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\\CoreBundle\\Event\\GetImageEvent', $this->event);
    }

    /**
     * Tests the setters and getters.
     */
    public function testSetterGetter()
    {
        $this->assertNull($this->event->getReturn());
        $this->assertEquals('orig/test.jpg', $this->event->getOriginalPath());
        $this->assertEquals(200, $this->event->getTargetWidth());
        $this->assertEquals(150, $this->event->getTargetHeight());
        $this->assertEquals('crop', $this->event->getResizeMode());
        $this->assertEquals('d/test-a235d.jpg', $this->event->getCacheName());
        $this->assertInstanceOf('Contao\\File', $this->event->getFileObject());
        $this->assertEquals('assets/images/d/test-a235d.jpg', $this->event->getTargetPath());
        $this->assertInstanceOf('Contao\\Image', $this->event->getImageObject());

        $fileObj  = new File('orig/test2.jpg');
        $imageObj = new Image($fileObj);

        $this->event->setReturn('assets/images/b/test2-f985b.jpg');
        $this->event->setOriginalPath('orig/test2.jpg');
        $this->event->setTargetWidth(400);
        $this->event->setTargetHeight(300);
        $this->event->setResizeMode('proportional');
        $this->event->setCacheName('b/test2-f985b.jpg');
        $this->event->setFileObject($fileObj);
        $this->event->setTargetPath('assets/images/b/test2-f985b.jpg');
        $this->event->setImageObject($imageObj);

        $this->assertEquals('assets/images/b/test2-f985b.jpg', $this->event->getReturn());
        $this->assertEquals('orig/test2.jpg', $this->event->getOriginalPath());
        $this->assertEquals(400, $this->event->getTargetWidth());
        $this->assertEquals(300, $this->event->getTargetHeight());
        $this->assertEquals('proportional', $this->event->getResizeMode());
        $this->assertEquals('b/test2-f985b.jpg', $this->event->getCacheName());
        $this->assertEquals($fileObj, $this->event->getFileObject());
        $this->assertEquals('assets/images/b/test2-f985b.jpg', $this->event->getTargetPath());
        $this->assertEquals($imageObj, $this->event->getImageObject());
    }

    /**
     * Tests passing arguments by reference.
     */
    public function testPassingArgumentsByReference()
    {
        $origPath     = 'orig/test.jpg';
        $targetWidth  = 200;
        $targetHeight = 150;
        $resizeMode   = 'crop';
        $cacheName    = 'd/test-a235d.jpg';
        $fileObj      = new File('orig/test.jpg');
        $targetPath   = 'assets/images/d/test-a235d.jpg';
        $imageObj     = new Image($fileObj);

        $this->event = new GetImageEvent(
            $origPath,
            $targetWidth,
            $targetHeight,
            $resizeMode,
            $cacheName,
            $fileObj,
            $targetPath,
            $imageObj
        );

        $fileObj2  = new File('orig/test2.jpg');
        $imageObj2 = new Image($fileObj);

        // Try to change the original variables
        $origPath     = 'orig/test2.jpg';
        $targetWidth  = 400;
        $targetHeight = 300;
        $resizeMode   = 'proportional';
        $cacheName    = 'b/test2-f985b.jpg';
        $fileObj      = $fileObj2;
        $targetPath   = 'assets/images/b/test2-f985b.jpg';
        $imageObj     = $imageObj2;

        $this->assertEquals('orig/test2.jpg', $this->event->getOriginalPath());
        $this->assertEquals(400, $this->event->getTargetWidth());
        $this->assertEquals(300, $this->event->getTargetHeight());
        $this->assertEquals('proportional', $this->event->getResizeMode());
        $this->assertEquals('b/test2-f985b.jpg', $this->event->getCacheName());
        $this->assertEquals($fileObj2, $this->event->getFileObject());
        $this->assertEquals('assets/images/b/test2-f985b.jpg', $this->event->getTargetPath());
        $this->assertEquals($imageObj2, $this->event->getImageObject());
    }
}
