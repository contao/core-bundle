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
     * The contao.execute_resize event is triggered when an image is resized.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\PageEvent
     */
    const EXECUTE_RESIZE = 'contao.execute_resize';

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
     * The contao.generate_xml_files event is triggered when the automator
     * regenerates the XML files.
     *
     * @var string
     *
     * @see Symfony\Component\EventDispatcher\Event
     */
    const GENERATE_XML_FILES = 'contao.generate_xml_files';

    /**
     * The contao.get_article event is triggered when an article is generated
     * in the Controller::getArticle() method.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_ARTICLE = 'contao.get_article';

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
     * The contao.get_root_page_from_url event is triggered when the root page
     * is read from the URL.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_ROOT_PAGE_FROM_URL = 'contao.get_root_page_from_url';

    /**
     * The contao.load_data_container event is triggered when a data container
     * is loaded.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const LOAD_DATA_CONTAINER = 'contao.load_data_container';

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

    /**
     * The contao.parse_template event is triggered right before a template is
     * parsed. It allows to modify the template object.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const PARSE_TEMPLATE = 'contao.parse_template';

    /**
     * The contao.post_authenticate event is triggered after a user has been
     * authenticated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const POST_AUTHENTICATE = 'contao.post_authenticate';

    /**
     * The contao.post_download event is triggered before a file is sent to the
     * client.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const POST_DOWNLOAD = 'contao.post_download';

    /**
     * The contao.post_login event is triggered after a user has logged in.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const POST_LOGIN = 'contao.post_login';

    /**
     * The contao.post_logout event is triggered after a user has logged out.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const POST_LOGOUT = 'contao.post_logout';

    /**
     * The contao.remove_old_feeds event is triggered when the automator
     * removes old XML feed files from the /share directory.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const REMOVE_OLD_FEEDS = 'contao.remove_old_feeds';

    /**
     * The contao.replace_dynamic_script_tags event is triggered when the
     * dynamic script tags are replaced.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const REPLACE_DYNAMIC_SCRIPT_TAGS = 'contao.replace_dynamic_script_tags';

    /**
     * The contao.set_cookie event is triggered when a cookie is set.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const SET_COOKIE = 'contao.set_cookie';
}
