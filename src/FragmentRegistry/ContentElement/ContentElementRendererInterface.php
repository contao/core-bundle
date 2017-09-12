<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\ContaoCoreBundle;

/**
 * Interface ContentElementRendererInterface
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface ContentElementRendererInterface
{
    /**
     * @param ContentModel $contentModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return bool
     */
    public function supports(ContentModel $contentModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool;

    /**
     * @param ContentModel $contentModel
     * @param string      $inColumn
     * @param string      $scope
     *
     * @return null|string
     */
    public function render(ContentModel $contentModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string;
}
