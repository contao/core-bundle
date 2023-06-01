<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\DependencyInjection\Attribute;

/**
 * An attribute to register a DCA callback.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsCallback
{
    public function __construct(
        public string $table,
        public string $target,
        public string|null $method = null,
        public int|null $priority = null,
    ) {
    }
}
