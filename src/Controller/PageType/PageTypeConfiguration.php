<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\CoreBundle\Controller\FragmentRegistry\ConfigurationInterface;
use Contao\PageModel;

/**
 * Class PageTypeConfiguration
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PageTypeConfiguration implements ConfigurationInterface
{
    /**
     * @var PageModel
     */
    private $pageModel;

    /**
     * @return PageModel
     */
    public function getPageModel()
    {
        return $this->pageModel;
    }

    /**
     * @param PageModel $pageModel
     *
     * @return PageTypeConfiguration
     */
    public function setPageModel(PageModel $pageModel)
    {
        $this->pageModel = $pageModel;

        return $this;
    }
}
