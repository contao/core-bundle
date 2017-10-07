<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Image;

use Contao\BackendUser;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\ImageSizesEvent;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ImageSizes
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Connection               $connection
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher, ContaoFrameworkInterface $framework)
    {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->framework = $framework;
    }

    /**
     * Returns the image sizes as options suitable for widgets.
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        $this->loadOptions();

        $event = new ImageSizesEvent($this->options);

        $this->eventDispatcher->dispatch(ContaoCoreEvents::IMAGE_SIZES_ALL, $event);

        return $event->getImageSizes();
    }

    /**
     * Returns the image sizes for the given user suitable for widgets.
     *
     * @param BackendUser $user
     *
     * @return array
     */
    public function getOptionsForUser(BackendUser $user): array
    {
        $this->loadOptions();

        if ($user->isAdmin) {
            $event = new ImageSizesEvent($this->options, $user);
        } else {
            $options = array_map(
                function ($val) {
                    return is_numeric($val) ? (int) $val : $val;
                },
                \StringUtil::deserialize($user->imageSizes, true)
            );

            $event = new ImageSizesEvent($this->filterOptions($options), $user);
        }

        $this->eventDispatcher->dispatch(ContaoCoreEvents::IMAGE_SIZES_USER, $event);

        return $event->getImageSizes();
    }

    /**
     * Loads the options from the database.
     */
    private function loadOptions(): void
    {
        if (null !== $this->options) {
            return;
        }

        // The framework is required to have the TL_CROP options available
        $this->framework->initialize();

        $this->options = $GLOBALS['TL_CROP'];

        $rows = $this->connection->fetchAll(
            'SELECT id, name, width, height FROM tl_image_size ORDER BY pid, name'
        );

        foreach ($rows as $imageSize) {
            $this->options['image_sizes'][$imageSize['id']] = sprintf(
                '%s (%sx%s)',
                $imageSize['name'],
                $imageSize['width'],
                $imageSize['height']
            );
        }
    }

    /**
     * Filters the options by the given allowed sizes and returns the result.
     *
     * @param array $allowedSizes
     *
     * @return array
     */
    private function filterOptions(array $allowedSizes): array
    {
        if (empty($allowedSizes)) {
            return [];
        }

        $filteredSizes = [];

        foreach ($this->options as $group => $sizes) {
            if ('image_sizes' === $group) {
                $this->filterImageSizes($sizes, $allowedSizes, $filteredSizes, $group);
            } else {
                $this->filterResizeModes($sizes, $allowedSizes, $filteredSizes, $group);
            }
        }

        return $filteredSizes;
    }

    /**
     * Filters image sizes.
     *
     * @param array  $sizes
     * @param array  $allowedSizes
     * @param array  $filteredSizes
     * @param string $group
     */
    private function filterImageSizes(array $sizes, array $allowedSizes, array &$filteredSizes, string $group): void
    {
        foreach ($sizes as $key => $size) {
            if (\in_array($key, $allowedSizes, true)) {
                $filteredSizes[$group][$key] = $size;
            }
        }
    }

    /**
     * Filters resize modes.
     *
     * @param array  $sizes
     * @param array  $allowedSizes
     * @param array  $filteredSizes
     * @param string $group
     */
    private function filterResizeModes(array $sizes, array $allowedSizes, array &$filteredSizes, string $group): void
    {
        foreach ($sizes as $size) {
            if (\in_array($size, $allowedSizes, true)) {
                $filteredSizes[$group][] = $size;
            }
        }
    }
}
