<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\PageModel;

/**
 * Class PageTypeConfiguration
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface PageTypeConfigurationInterface
{
    /**
     * @return PageModel
     */
    public function getPageModel();
}
