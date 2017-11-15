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

use Contao\ModuleModel;

class FrontendModuleReference extends FragmentReference
{
    public const TAG_NAME = 'contao.frontend_module';

    /**
     * Constructor.
     *
     * @param ModuleModel $model
     * @param string      $inColumn
     */
    public function __construct(ModuleModel $model, string $inColumn = 'main')
    {
        $this->attributes['moduleModel'] = $model->id;
        $this->attributes['inColumn'] = $inColumn;

        parent::__construct(self::TAG_NAME.'.'.$model->type);
    }
}
