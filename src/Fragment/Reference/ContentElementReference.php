<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Fragment\Reference;

use Contao\ContentModel;

class ContentElementReference extends FragmentReference
{
    public const TAG_NAME = 'contao.content_element';

    /**
     * Constructor.
     *
     * @param ContentModel $model
     * @param string       $inColumn
     */
    public function __construct(ContentModel $model, string $inColumn = 'main')
    {
        parent::__construct(self::TAG_NAME.'.'.$model->type);

        $this->attributes['contentModel'] = $model->id;
        $this->attributes['inColumn'] = $inColumn;
    }
}
