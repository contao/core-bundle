<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Image;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image\Picture;
use Contao\Image\PictureGeneratorInterface;
use Contao\Image\PictureConfiguration;
use Contao\Image\ResizeConfiguration;
use Contao\Image\ResizeOptions;
use Contao\Image\PictureConfigurationItem;

/**
 * Creates Picture objects.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class PictureFactory
{
    /**
     * @var PictureGeneratorInterface
     */
    private $pictureGenerator;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var bool
     */
    private $bypassCache;

    /**
     * @var array
     */
    private $imagineOptions;

    /**
     * Constructor.
     *
     * @param PictureGeneratorInterface $pictureGenerator The picture generator
     * @param ImageFactory              $imageFactory     The image factory
     * @param ContaoFrameworkInterface  $framework        The Contao framework
     * @param bool                      $bypassCache      True to bypass the image cache
     * @param array                     $imagineOptions   The options for Imagine save
     */
    public function __construct(
        PictureGeneratorInterface $pictureGenerator,
        ImageFactory $imageFactory,
        ContaoFrameworkInterface $framework,
        $bypassCache,
        array $imagineOptions
    ) {
        $this->pictureGenerator = $pictureGenerator;
        $this->imageFactory = $imageFactory;
        $this->framework = $framework;
        $this->bypassCache = (bool) $bypassCache;
        $this->imagineOptions = $imagineOptions;
    }

    /**
     * Creates a Picture object.
     *
     * @param string    $path The path to the source image
     * @param int|array $size The ID of an image size or an array with width
     *                        height and resize mode
     *
     * @return Picture The created Picture object
     */
    public function create($path, $size = null)
    {
        $attributes = [];

        if (is_array($size) && isset($size[2]) && substr_count($size[2], '_') === 1) {
            $image = $this->imageFactory->create($path, $size);
            $config = new PictureConfiguration();
        }
        else {
            $image = $this->imageFactory->create($path);
            list($config, $attributes) = $this->createConfig($size);
        }

        $picture = $this->pictureGenerator->generate(
            $image,
            $config,
            (new ResizeOptions())
                ->setImagineOptions($this->imagineOptions)
                ->setBypassCache($this->bypassCache)
        );

        if (count($attributes)) {
            $img = $picture->getImg();
            foreach ($attributes as $attribute => $value) {
                $img[$attribute] = $value;
            }
            $picture = new Picture($img, $picture->getSources());
        }

        return $picture;
    }

    private function createConfig($size)
    {
        if (!is_array($size)) {
            $size = [0, 0, $size];
        }

        $config = new PictureConfiguration();
        $attributes = [];

        if (!isset($size[2]) || !is_numeric($size[2])) {
            $resizeConfig = new ResizeConfiguration();
            if (isset($size[0]) && $size[0]) {
                $resizeConfig->setWidth($size[0]);
            }
            if (isset($size[1]) && $size[1]) {
                $resizeConfig->setHeight($size[1]);
            }
            if (isset($size[2]) && $size[2]) {
                $resizeConfig->setMode($size[2]);
            }
            $configItem = new PictureConfigurationItem();
            $configItem->setResizeConfig($resizeConfig);
            $config->setSize($configItem);

            return [$config, $attributes];
        }

        $imageSizeModel = $this->framework
            ->getAdapter('Contao\\ImageSizeModel')
            ->findByPk($size[2]);

        $config->setSize($this->createConfigItem($imageSizeModel));

        if ($imageSizeModel && $imageSizeModel->cssClass) {
            $attributes['class'] = $imageSizeModel->cssClass;
        }

        $imageSizeItems = $this->framework
            ->getAdapter('Contao\\ImageSizeItemModel')
            ->findVisibleByPid($size[2], ['order' => 'sorting ASC']);

        if ($imageSizeItems !== null) {
            $configItems = [];
            foreach ($imageSizeItems as $imageSizeItem) {
                $configItems[] = $this->createConfigItem($imageSizeItem);
            }
            $config->setSizeItems($configItems);
        }

        return [$config, $attributes];
    }

    private function createConfigItem($imageSize)
    {
        $configItem = new PictureConfigurationItem();
        $resizeConfig = new ResizeConfiguration();

        if (null !== $imageSize) {
            $resizeConfig
                ->setWidth($imageSize->width)
                ->setHeight($imageSize->height)
                ->setMode($imageSize->resizeMode)
                ->setZoomLevel($imageSize->zoom);

            $configItem
                ->setResizeConfig($resizeConfig)
                ->setSizes($imageSize->sizes)
                ->setDensities($imageSize->densities);

            if (isset($imageSize->media)) {
                $configItem->setMedia($imageSize->media);
            }
        }

        return $configItem;
    }
}
