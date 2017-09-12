<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\AbstractFragmentRenderer;
use Contao\PageModel;

/**
 * Class DefaultPageTypeRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DefaultPageTypeRenderer extends AbstractFragmentRenderer implements PageTypeRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(PageModel $pageModel): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function render(PageModel $pageModel): ?string
    {
        $fragmentIdentifier = 'contao.page_type.' . $pageModel->type;

        return $this->renderDefault($fragmentIdentifier, [], [], 'inline');
    }
}
