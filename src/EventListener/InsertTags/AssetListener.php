<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\InsertTags;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Component\Asset\Packages;

/**
 * @internal
 */
#[AsHook('replaceInsertTags')]
class AssetListener
{
    public function __construct(private readonly Packages $packages)
    {
    }

    /**
     * Replaces the "asset" insert tag.
     */
    public function onReplaceInsertTags(string $tag): string|false
    {
        $chunks = explode('::', $tag);

        if ('asset' !== $chunks[0]) {
            return false;
        }

        return $this->packages->getUrl($chunks[1], $chunks[2] ?? null);
    }
}
