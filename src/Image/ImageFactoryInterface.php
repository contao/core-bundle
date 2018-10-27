<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Image;

use Contao\Image\ImageInterface;
use Contao\Image\ImportantPartInterface;
use Contao\Image\ResizeConfigurationInterface;

interface ImageFactoryInterface
{
    /**
     * Creates an Image object.
     *
     * @param string|ImageInterface                       $path       The absolute path to the source image or an Image object
     * @param int|array|ResizeConfigurationInterface|null $size       An image size ID, an array with width, height and resize mode or a ResizeConfiguration object
     * @param string|null                                 $targetPath
     *
     * @return ImageInterface
     */
    public function create($path, $size = null, $targetPath = null);

    /**
     * Returns the equivalent important part from a legacy resize mode.
     *
     * @param string $mode One of left_top, center_top, right_top, left_center, center_center, right_center, left_bottom, center_bottom, right_bottom
     *
     * @return ImportantPartInterface
     */
    public function getImportantPartFromLegacyMode(ImageInterface $image, $mode);
}
