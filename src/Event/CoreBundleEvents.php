<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Event;

class CoreBundleEvents
{
    /**
     * The CREATE_PAGE_ROUTES event occurs when CMF provider generate the page routes.
     *
     * This event allows you to create routes for specific page types.
     * The event listener method receives a Contao\CoreBundle\Event\CreatePageRouteEvent instance.
     *
     * @var string
     *
     * @api
     */
    const CREATE_PAGE_ROUTES = 'contao_core_module.create_page_route';
}
