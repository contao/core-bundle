<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\FrontendModule;

use Contao\PageModel;

/**
 * Interface PageTypeRendererInterface
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface PageTypeRendererInterface
{
    /**
     * @param PageModel $pageModel
     *
     * @return bool
     */
    public function supports(PageModel $pageModel): bool;

    /**
     * @param PageModel $pageModel
     *
     * @return string|null
     */
    public function render(PageModel $pageModel): ?string;

}
