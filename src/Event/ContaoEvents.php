<?php

/*
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
     * The contao.generate_page event is triggered when the page object
     * is generated in the front end.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\PageEvent
     */
    const GENERATE_PAGE = 'contao.generate_page';

    /**
     * The contao.get_cache_key event is triggered when the name of a front
     * end cache file is calculated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_CACHE_KEY = 'contao.get_cache_key';

    /**
     * The contao.get_page_id_from_url event is triggered when the page ID is
     * read from the URL.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_PAGE_ID_FROM_URL = 'contao.get_page_id_from_url';

    /**
     * The contao.get_page_layout event is triggered after the front end
     * layout object has been built.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\PageEvent
     */
    const GET_PAGE_LAYOUT = 'contao.get_page_layout';

    /**
     * The contao.modify_frontend_page event is triggered after a front end
     * template has been compiled.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const MODIFY_FRONTEND_PAGE = 'contao.modify_frontend_page';

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

    /**
     * The contao.output_backend_template event is triggered when a back end
     * template is send to the client.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const OUTPUT_BACKEND_TEMPLATE = 'contao.output_backend_template';

    /**
     * The contao.output_frontend_template event is triggered when a front end
     * template is send to the client.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const OUTPUT_FRONTEND_TEMPLATE = 'contao.output_frontend_template';
}
