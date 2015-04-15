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
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetCacheKeyEvent
     */
    const GET_CACHE_KEY = 'contao.get_cache_key';

    /**
     * The contao.initialize_system event is triggered when the Contao
     * framework is initialized.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\InitializeSystemEvent
     */
    const INITIALIZE_SYSTEM = 'contao.initialize_system';

    /**
     * The contao.parse_backend_template event is triggered when a back end
     * template is parsed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const PARSE_BACKEND_TEMPLATE = 'contao.parse_backend_template';

    /**
     * The contao.parse_frontend_template event is triggered when a front end
     * template is parsed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const PARSE_FRONTEND_TEMPLATE = 'contao.parse_frontend_template';
}
