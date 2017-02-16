<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\CoreBundle\Controller\FragmentRegistry\Configuration;
use Contao\PageModel;

/**
 * Class PageTypeConfiguration
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class PageTypeConfiguration extends Configuration
{
    /**
     * @var PageModel|null
     */
    private $pageModel;

    /**
     * @return PageModel|null
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

    /**
     * Add pageId to query parameters by default if page model
     * was set.
     *
     * @return array
     */
    public function getQueryParameters()
    {
        $params = parent::getQueryParameters();

        if ($this->pageModel instanceof PageModel) {
            $params['pageId'] = $this->pageModel->id;
        }

        return $params;
    }
}
