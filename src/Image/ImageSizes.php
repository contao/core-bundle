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

use Contao\BackendUser;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\ImageSizesEvent;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImageSizes implements ResetInterface
{
    private array $predefinedSizes = [];

    private array|null $options = null;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Sets the predefined image sizes.
     */
    public function setPredefinedSizes(array $predefinedSizes): void
    {
        $this->predefinedSizes = $predefinedSizes;
    }

    /**
     * Returns the image sizes as options suitable for widgets.
     *
     * @return array<string, array<string>>
     */
    public function getAllOptions(): array
    {
        $this->loadOptions();

        $event = new ImageSizesEvent($this->options);

        $this->eventDispatcher->dispatch($event, ContaoCoreEvents::IMAGE_SIZES_ALL);

        return $event->getImageSizes();
    }

    /**
     * Returns the image sizes for the given user suitable for widgets.
     *
     * @return array<string, array<string>>
     */
    public function getOptionsForUser(BackendUser $user): array
    {
        $this->loadOptions();

        if ($user->isAdmin) {
            $event = new ImageSizesEvent($this->options, $user);
        } else {
            $options = array_map(
                static fn ($val) => is_numeric($val) ? (int) $val : $val,
                StringUtil::deserialize($user->imageSizes, true),
            );

            $event = new ImageSizesEvent($this->filterOptions($options), $user);
        }

        $this->eventDispatcher->dispatch($event, ContaoCoreEvents::IMAGE_SIZES_USER);

        return $event->getImageSizes();
    }

    public function reset(): void
    {
        $this->options = null;
    }

    /**
     * Loads the options from the database.
     */
    private function loadOptions(): void
    {
        if (null !== $this->options) {
            return;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT
                s.id, s.name, s.width, s.height, t.name as theme
            FROM
                tl_image_size s
            LEFT JOIN
                tl_theme t ON s.pid=t.id
            ORDER BY
                s.pid, s.name',
        );

        $options = [];

        foreach ($this->predefinedSizes as $name => $imageSize) {
            $options['image_sizes'][$name] = sprintf(
                '%s (%sx%s)',
                $this->translator->trans(substr($name, 1), [], 'image_sizes') ?: substr($name, 1),
                $imageSize['width'] ?? '',
                $imageSize['height'] ?? '',
            );
        }

        foreach ($rows as $imageSize) {
            // Prefix theme names that are numeric or collide with existing group names
            if (is_numeric($imageSize['theme']) || \in_array($imageSize['theme'], ['custom', 'image_sizes', 'exact', 'relative'], true)) {
                $imageSize['theme'] = 'Theme '.$imageSize['theme'];
            }

            if (!isset($options[$imageSize['theme']])) {
                $options[$imageSize['theme']] = [];
            }

            $options[$imageSize['theme']][$imageSize['id']] = sprintf(
                '%s (%sx%s)',
                $imageSize['name'],
                $imageSize['width'],
                $imageSize['height'],
            );
        }

        $this->options = array_merge_recursive($options, [
            'image_sizes' => [],
            'custom' => ['crop', 'proportional', 'box'],
        ]);
    }

    /**
     * Filters the options by the given allowed sizes and returns the result.
     *
     * @return array<string, array<string>>
     */
    private function filterOptions(array $allowedSizes): array
    {
        if (!$allowedSizes) {
            return [];
        }

        $filteredSizes = [];

        foreach ($this->options as $group => $sizes) {
            if ('custom' === $group || 'relative' === $group || 'exact' === $group) {
                $this->filterResizeModes($sizes, $allowedSizes, $filteredSizes, $group);
            } else {
                $this->filterImageSizes($sizes, $allowedSizes, $filteredSizes, $group);
            }
        }

        return $filteredSizes;
    }

    private function filterImageSizes(array $sizes, array $allowedSizes, array &$filteredSizes, string $group): void
    {
        foreach ($sizes as $key => $size) {
            if (\in_array($key, $allowedSizes, true)) {
                $filteredSizes[$group][$key] = $size;
            }
        }
    }

    private function filterResizeModes(array $sizes, array $allowedSizes, array &$filteredSizes, string $group): void
    {
        foreach ($sizes as $size) {
            if (\in_array($size, $allowedSizes, true)) {
                $filteredSizes[$group][] = $size;
            }
        }
    }
}
