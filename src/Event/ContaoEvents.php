<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

/**
 * Defines Constants for all Contao events.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
final class ContaoEvents
{
    /**
     * The contao.get_cache_key event is triggered when the name of a front
     * end cache file is calculated.
     *
     * The event listener method receives a
     * Contao\CoreBundle\Event\GetCacheKeyEvent instance.
     *
     * @var string
     */
    const GET_CACHE_KEY = 'contao.get_cache_key';

    /**
     * The contao.initialize_system event is triggered when the Contao
     * framework is initialized.
     *
     * The event listener method receives a
     * Contao\CoreBundle\Event\InitializeSystemEvent instance.
     *
     * @var string
     */
    const INITIALIZE_SYSTEM = 'contao.initialize_system';

    /**
     * The contao.parse_frontend_template event is triggered when a front end
     * template is parsed.
     *
     * The event listener method receives a
     * Contao\CoreBundle\Event\TemplateEvent instance.
     *
     * @var string
     */
    const PARSE_FRONTEND_TEMPLATE = 'contao.parse_frontend_template';
}
