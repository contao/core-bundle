<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\File;
use Contao\Image;

/**
 * Allows to execute logic when an image is resized.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class GetImageEvent extends ReturnValueEvent
{
    /**
     * @var string
     */
    private $origPath;

    /**
     * @var int
     */
    private $targetWidth;

    /**
     * @var int
     */
    private $targetHeight;

    /**
     * @var string
     */
    private $resizeMode;

    /**
     * @var string
     */
    private $cacheName;

    /**
     * @var File
     */
    private $fileObj;

    /**
     * @var string
     */
    private $targetPath;

    /**
     * @var Image
     */
    private $imageObj;

    /**
     * Constructor.
     *
     * @param string $origPath     The original path
     * @param int    $targetWidth  The target width
     * @param int    $targetHeight The target height
     * @param string $resizeMode   The resize mode
     * @param string $cacheName    The cache name
     * @param File   $fileObj      The file object
     * @param string $targetPath   The target path
     * @param Image  $imageObj     The image object
     */
    public function __construct(
        $origPath,
        $targetWidth,
        $targetHeight,
        $resizeMode,
        $cacheName,
        File $fileObj,
        $targetPath,
        Image $imageObj
    ) {
        $this->origPath = $origPath;
        $this->targetWidth = $targetWidth;
        $this->targetHeight = $targetHeight;
        $this->resizeMode = $resizeMode;
        $this->cacheName = $cacheName;
        $this->fileObj = $fileObj;
        $this->targetPath = $targetPath;
        $this->imageObj = $imageObj;
    }

    /**
     * Returns the original path.
     *
     * @return string The original path
     */
    public function getOriginalPath()
    {
        return $this->origPath;
    }

    /**
     * Returns the target width.
     *
     * @return int The target width
     */
    public function getTargetWidth()
    {
        return $this->targetWidth;
    }

    /**
     * Returns the target height.
     *
     * @return int The target height
     */
    public function getTargetHeight()
    {
        return $this->targetHeight;
    }

    /**
     * Returns the resize mode.
     *
     * @return string The resize mode
     */
    public function getResizeMode()
    {
        return $this->resizeMode;
    }

    /**
     * Returns the cache name.
     *
     * @return string The cache name
     */
    public function getCacheName()
    {
        return $this->cacheName;
    }

    /**
     * Returns the file object.
     *
     * @return File The file object
     */
    public function getFileObject()
    {
        return $this->fileObj;
    }

    /**
     * Returns the target path.
     *
     * @return string The target path
     */
    public function getTargetPath()
    {
        return $this->targetPath;
    }

    /**
     * Returns the image object.
     *
     * @return Image The image object
     */
    public function getImageObject()
    {
        return $this->imageObj;
    }
}
