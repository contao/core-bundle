<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Image;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Image\ImageInterface;
use Contao\Image\Picture;
use Contao\Image\PictureConfiguration;
use Contao\Image\PictureConfigurationInterface;
use Contao\Image\PictureConfigurationItem;
use Contao\Image\PictureGeneratorInterface;
use Contao\Image\PictureInterface;
use Contao\Image\ResizeConfiguration;
use Contao\Image\ResizeConfigurationInterface;
use Contao\Image\ResizeOptions;
use Contao\ImageSizeItemModel;
use Contao\ImageSizeModel;

/**
 * Creates Picture objects.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class PictureFactory implements PictureFactoryInterface
{
    const ASPECT_RATIO_THRESHOLD = 0.05;

    /**
     * @var array
     */
    private $imageSizeItemsCache = [];

    /**
     * @var PictureGeneratorInterface
     */
    private $pictureGenerator;

    /**
     * @var ImageFactoryInterface
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
     * @var string
     */
    private $defaultDensities = '';

    /**
     * Constructor.
     *
     * @param PictureGeneratorInterface $pictureGenerator
     * @param ImageFactoryInterface     $imageFactory
     * @param ContaoFrameworkInterface  $framework
     * @param bool                      $bypassCache
     * @param array                     $imagineOptions
     */
    public function __construct(PictureGeneratorInterface $pictureGenerator, ImageFactoryInterface $imageFactory, ContaoFrameworkInterface $framework, $bypassCache, array $imagineOptions)
    {
        $this->pictureGenerator = $pictureGenerator;
        $this->imageFactory = $imageFactory;
        $this->framework = $framework;
        $this->bypassCache = (bool) $bypassCache;
        $this->imagineOptions = $imagineOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDensities($densities)
    {
        $this->defaultDensities = (string) $densities;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create($path, $size = null)
    {
        $attributes = [];

        if ($path instanceof ImageInterface) {
            $image = $path;
        } else {
            $image = $this->imageFactory->create($path);
        }

        if (\is_array($size) && isset($size[2]) && \is_string($size[2]) && 1 === substr_count($size[2], '_')) {
            $image->setImportantPart($this->imageFactory->getImportantPartFromLegacyMode($image, $size[2]));
            $size[2] = ResizeConfigurationInterface::MODE_CROP;
        }

        if ($size instanceof PictureConfigurationInterface) {
            $config = $size;
        } else {
            list($config, $attributes) = $this->createConfig($size);
        }

        $picture = $this->pictureGenerator->generate(
            $image,
            $config,
            (new ResizeOptions())->setImagineOptions($this->imagineOptions)->setBypassCache($this->bypassCache)
        );

        $attributes['hasSingleAspectRatio'] = $this->hasSingleAspectRatio($picture);

        return $this->addImageAttributes($picture, $attributes);
    }

    /**
     * Creates a picture configuration.
     *
     * @param int|array|null $size
     *
     * @return array<PictureConfiguration,array>
     */
    private function createConfig($size)
    {
        if (!\is_array($size)) {
            $size = [0, 0, $size];
        }

        $config = new PictureConfiguration();
        $attributes = [];

        if (!isset($size[2]) || !is_numeric($size[2])) {
            $resizeConfig = new ResizeConfiguration();

            if (!empty($size[0])) {
                $resizeConfig->setWidth($size[0]);
            }

            if (!empty($size[1])) {
                $resizeConfig->setHeight($size[1]);
            }

            if (!empty($size[2])) {
                $resizeConfig->setMode($size[2]);
            }

            $configItem = new PictureConfigurationItem();
            $configItem->setResizeConfig($resizeConfig);

            if ($this->defaultDensities) {
                $configItem->setDensities($this->defaultDensities);
            }

            $config->setSize($configItem);

            return [$config, $attributes];
        }

        /** @var ImageSizeModel $imageSizeModel */
        $imageSizeModel = $this->framework->getAdapter(ImageSizeModel::class);
        $imageSizes = $imageSizeModel->findByPk($size[2]);

        $config->setSize($this->createConfigItem($imageSizes));

        if ($imageSizes && $imageSizes->cssClass) {
            $attributes['class'] = $imageSizes->cssClass;
        }

        if (!\array_key_exists($size[2], $this->imageSizeItemsCache)) {
            /** @var ImageSizeItemModel $adapter */
            $adapter = $this->framework->getAdapter(ImageSizeItemModel::class);
            $this->imageSizeItemsCache[$size[2]] = $adapter->findVisibleByPid($size[2], ['order' => 'sorting ASC']);
        }

        $imageSizeItems = $this->imageSizeItemsCache[$size[2]];

        if (null !== $imageSizeItems) {
            $configItems = [];

            foreach ($imageSizeItems as $imageSizeItem) {
                $configItems[] = $this->createConfigItem($imageSizeItem);
            }

            $config->setSizeItems($configItems);
        }

        return [$config, $attributes];
    }

    /**
     * Creates a picture configuration item.
     *
     * @param ImageSizeModel|ImageSizeItemModel|null $imageSize
     *
     * @return PictureConfigurationItem
     */
    private function createConfigItem($imageSize)
    {
        $configItem = new PictureConfigurationItem();
        $resizeConfig = new ResizeConfiguration();

        if (null !== $imageSize) {
            $resizeConfig
                ->setWidth($imageSize->width)
                ->setHeight($imageSize->height)
                ->setMode($imageSize->resizeMode)
                ->setZoomLevel($imageSize->zoom)
            ;

            $configItem
                ->setResizeConfig($resizeConfig)
                ->setSizes($imageSize->sizes)
                ->setDensities($imageSize->densities)
            ;

            if (isset($imageSize->media)) {
                $configItem->setMedia($imageSize->media);
            }
        }

        return $configItem;
    }

    /**
     * Adds the image attributes.
     *
     * @param PictureInterface $picture
     * @param array            $attributes
     *
     * @return PictureInterface
     */
    private function addImageAttributes(PictureInterface $picture, array $attributes)
    {
        if (empty($attributes)) {
            return $picture;
        }

        $img = $picture->getImg();

        foreach ($attributes as $attribute => $value) {
            $img[$attribute] = $value;
        }

        return new Picture($img, $picture->getSources());
    }

    /**
     * Returns true if the aspect ratios of all sources of the picture are
     * nearly the same and differ less than the ASPECT_RATIO_THRESHOLD.
     */
    private function hasSingleAspectRatio(PictureInterface $picture)
    {
        if (0 === \count($picture->getSources())) {
            return true;
        }

        $img = $picture->getImg();

        if (empty($img['width']) || empty($img['height'])) {
            return false;
        }

        foreach ($picture->getSources() as $source) {
            if (empty($source['width']) || empty($source['height'])) {
                return false;
            }

            $diffA = abs(($img['width'] / $img['height']) / ($source['width'] / $source['height']) - 1);
            $diffB = abs(($img['height'] / $img['width']) / ($source['height'] / $source['width']) - 1);

            if ($diffA > self::ASPECT_RATIO_THRESHOLD && $diffB > self::ASPECT_RATIO_THRESHOLD) {
                return false;
            }
        }

        return true;
    }
}
