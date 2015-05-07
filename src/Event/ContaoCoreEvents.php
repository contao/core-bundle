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
final class ContaoCoreEvents
{
    /**
     * The contao.add_custom_regexp event is triggered when a custom regular
     * expression is found.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\AddCustomRegexpEvent
     */
    const ADD_CUSTOM_REGEXP = 'contao.add_custom_regexp';

    /**
     * The contao.add_log_entry event is triggered when a log entry is added.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\AddLogEntryEvent
     */
    const ADD_LOG_ENTRY = 'contao.add_log_entry';

    /**
     * The contao.check_credentials event is triggered when the login
     * credentials are checked.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\CheckCredentialsEvent
     */
    const CHECK_CREDENTIALS = 'contao.check_credentials';

    /**
     * The contao.execute_resize event is triggered when an image is resized.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\PageEvent
     */
    const EXECUTE_RESIZE = 'contao.execute_resize';

    /**
     * The contao.generate_frontend_url event is triggered when a front end URL
     * is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GenerateFrontendUrlEvent
     */
    const GENERATE_FRONTEND_URL = 'contao.generate_frontend_url';

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
     * The contao.get_combined_file event is triggered when a combined .css or
     * .js file is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_COMBINED_FILE = 'contao.get_combined_file';

    /**
     * The contao.get_content_element event is triggered when a content element
     * is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetContentElementEvent
     */
    const GET_CONTENT_ELEMENT = 'contao.get_content_element';

    /**
     * The contao.get_countries event is triggered when the country list is
     * compiled.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetCountriesEvent
     */
    const GET_COUNTRIES = 'contao.get_countries';

    /**
     * The contao.get_frontend_module event is triggered when a front end
     * module is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetFrontendModuleEvent
     */
    const GET_FRONTEND_MODULE = 'contao.get_frontend_module';

    /**
     * The contao.get_form event is triggered when a form is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetFormEvent
     */
    const GET_FORM = 'contao.get_form';

    /**
     * The contao.get_image event is triggered when an image is resized.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetImageEvent
     */
    const GET_IMAGE = 'contao.get_image';

    /**
     * The contao.get_languages event is triggered when the language list is
     * compiled.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetLanguagesEvent
     */
    const GET_LANGUAGES = 'contao.get_languages';

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
     * The contao.get_page_status_icon event is triggered when a page status
     * icon is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetPageStatusIconEvent
     */
    const GET_PAGE_STATUS_ICON = 'contao.get_page_status_icon';

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
     * The contao.get_searchable_pages event is triggered when the searchable
     * pages are compiled.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const GET_SEARCHABLE_PAGES = 'contao.get_searchable_pages';

    /**
     * The contao.get_user_navigation event is triggered when the back end
     * navigation is generated.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\GetUserNavigationEvent
     */
    const GET_USER_NAVIGATION = 'contao.get_user_navigation';

    /**
     * The contao.import_user event is triggered to import a user.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ImportUserEvent
     */
    const IMPORT_USER = 'contao.import_user';

    /**
     * The contao.index_page event is triggered when a front end page is
     * indexed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\IndexPageEvent
     */
    const INDEX_PAGE = 'contao.index_page';

    /**
     * The contao.is_visible_element event is triggered when checking an
     * element for visibility.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\IsVisibleElementEvent
     */
    const IS_VISIBLE_ELEMENT = 'contao.is_visible_element';

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
     * The contao.load_language_file event is triggered when a language file is
     * loaded.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\LoadLanguageFileEvent
     */
    const LOAD_LANGUAGE_FILE = 'contao.load_language_file';

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
     * The contao.parse_backend_template event is triggered when a back end
     * template is parsed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\TemplateEvent
     */
    const PARSE_BACKEND_TEMPLATE = 'contao.parse_backend_template';

    /**
     * The contao.parse_date event is triggered when a date is parsed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ParseDateEvent
     */
    const PARSE_DATE = 'contao.parse_date';

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
     * The contao.parse_template event is triggered right before a template is
     * parsed. It allows to modify the template object.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const PARSE_TEMPLATE = 'contao.parse_template';

    /**
     * The contao.parse_widget event is triggered when a widget is parsed.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ParseWidgetEvent
     */
    const PARSE_WIDGET = 'contao.parse_widget';

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
     * The contao.revise_table event is triggered when a table is revised.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReviseTableEvent
     */
    const REVISE_TABLE = 'contao.revise_table';

    /**
     * The contao.set_cookie event is triggered when a cookie is set.
     *
     * @var string
     *
     * @see Contao\CoreBundle\Event\ReturnValueEvent
     */
    const SET_COOKIE = 'contao.set_cookie';
}
