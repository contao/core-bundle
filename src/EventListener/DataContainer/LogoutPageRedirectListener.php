<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

#[AsCallback(table: 'tl_page', target: 'fields.jumpTo.attributes')]
class LogoutPageRedirectListener
{
    public function __invoke(array $attributes, mixed $dc): array
    {
        if ($dc instanceof DataContainer && 'logout' === ($dc->getCurrentRecord()['type'] ?? null)) {
            $attributes['mandatory'] = true;
        }

        return $attributes;
    }
}
