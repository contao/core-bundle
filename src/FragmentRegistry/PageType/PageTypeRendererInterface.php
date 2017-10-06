<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\PageType;

use Contao\PageModel;

/**
 * Interface PageTypeRendererInterface.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface PageTypeRendererInterface
{
    /**
     * Checks if the renderer is supported.
     *
     * @param PageModel $pageModel
     *
     * @return bool
     */
    public function supports(PageModel $pageModel): bool;

    /**
     * Renders the fragment.
     *
     * @param PageModel $pageModel
     *
     * @return string|null
     */
    public function render(PageModel $pageModel): ?string;
}
