<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\PageType;

use Contao\PageModel;

/**
 * Class DelegatingPageTypeRenderer
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DelegatingPageTypeRenderer implements PageTypeRendererInterface
{
    /**
     * @var PageTypeRendererInterface[]
     */
    private $renderers;

    /**
     * DelegatingPageTypeRenderer constructor.
     *
     * @param PageTypeRendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = $renderers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PageModel $pageModel): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($pageModel)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(PageModel $pageModel): ?string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($pageModel)) {
                return $renderer->render($pageModel);
            }
        }

        return null;
    }
}
