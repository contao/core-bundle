<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Fragment\Reference;

class InsertTagReference extends FragmentReference
{
    public const TAG_NAME = 'contao.insert_tag';

    /**
     * @param string $insertTag
     * @param string $parameters
     * @param array  $flags
     * @param bool   $forceInlineRendering
     */
    public function __construct(string $insertTag, string $parameters = '', array $flags = [])
    {
        parent::__construct(self::TAG_NAME.'.'.$insertTag);

        $this->attributes['insertTag'] = $insertTag;
        $this->attributes['parameters'] = $parameters;
        $this->attributes['flags'] = $flags;
    }
}
