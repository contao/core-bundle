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

use Contao\ContentModel;

class ContentElementReference extends FragmentReference
{
    public const TAG_NAME = 'contao.content_element';

    public function __construct(ContentModel $model, string $section = 'main')
    {
        parent::__construct(self::TAG_NAME.'.'.$model->type);

        $this->attributes['contentModel'] = $model->id;
        $this->attributes['section'] = $section;
        $this->attributes['classes'] = $model->classes;
    }
}
