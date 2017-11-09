<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\InsertTags;

use Symfony\Component\Asset\Packages;

class AssetListener
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * Constructor.
     */
    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Replaces the {{asset::…}} insert tag.
     *
     * @param string $tag
     *
     * @return string|bool
     */
    public function onReplaceInsertTags(string $tag)
    {
        $chunks = explode('::', $tag);

        if ('asset' === $chunks[0]) {
            return $this->packages->getUrl((string) $chunks[1], $chunks[2] ?? null);
        }

        return false;
    }
}
