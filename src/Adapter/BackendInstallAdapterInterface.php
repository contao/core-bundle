<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Adapter;

/**
 * Provides an adapter interface for the Contao BackendInstall class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface BackendInstallAdapterInterface
{
    /**
     * Run the controller and parse the login template
     */
    public function run();

    /**
     * Return the current theme as string
     *
     * @return string The name of the theme
     */
    public function getTheme();

    /**
     * Return the back end themes as array
     *
     * @return array An array of available back end themes
     */
    public function getThemes();

    /**
     * Return the TinyMCE language
     *
     * @return string
     */
    public function getTinyMceLanguage();

    /**
     * Validate an ACE type
     *
     * @param string $type
     *
     * @return string
     */
    public function getAceType($type);

    /**
     * Return a list of TinyMCE templates as JSON string
     *
     * @return string
     */
    public function getTinyTemplates();

    /**
     * Add the request token to the URL
     *
     * @param string  $strRequest
     * @param boolean $blnAddRef
     * @param array   $arrUnset
     *
     * @return string
     */
    public function addToUrl($strRequest, $blnAddRef = true, $arrUnset = array());

    /**
     * Get all searchable pages and return them as array
     *
     * @param integer $pid
     * @param string  $domain
     * @param boolean $blnIsSitemap
     * @param string  $strLanguage
     *
     * @return array
     */
    public function findSearchablePages($pid = 0, $domain = '', $blnIsSitemap = false, $strLanguage = '');

    /**
     * Add a breadcrumb menu to the page tree
     *
     * @param string $strKey
     *
     * @throws \RuntimeException
     */
    public function addPagesBreadcrumb($strKey = 'tl_page_node');

    /**
     * Add an image to each page in the tree
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param string         $imageAttribute
     * @param boolean        $blnReturnImage
     * @param boolean        $blnProtected
     *
     * @return string
     */
    public function addPageIcon($row, $label, $dc = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false);

    /**
     * Add a breadcrumb menu to the file tree
     *
     * @param string $strKey
     *
     * @throws \RuntimeException
     */
    public function addFilesBreadcrumb($strKey = 'tl_files_node');

    /**
     * Get all allowed pages and return them as string
     *
     * @return string
     */
    public function createPageList();

    /**
     * Get all allowed files and return them as string
     *
     * @param string  $strFilter
     * @param boolean $filemount
     *
     * @return string
     */
    public function createFileList($strFilter = '', $filemount = false);

    /**
     * Find a particular template file and return its path
     *
     * @param string $strTemplate The name of the template
     * @param string $strFormat   The file extension
     *
     * @return string The path to the template file
     *
     * @throws \InvalidArgumentException If $strFormat is unknown
     * @throws \RuntimeException         If the template group folder is insecure
     */
    public function getTemplate($strTemplate, $strFormat = 'html5');

    /**
     * Return all template files of a particular group as array
     *
     * @param string $strPrefix The template name prefix (e.g. "ce_")
     *
     * @return array An array of template names
     */
    public function getTemplateGroup($strPrefix);

    /**
     * Generate a front end module and return it as string
     *
     * @param mixed  $intId     A module ID or a Model object
     * @param string $strColumn The name of the column
     *
     * @return string The module HTML markup
     */
    public function getFrontendModule($intId, $strColumn = 'main');

    /**
     * Generate an article and return it as string
     *
     * @param mixed   $varId          The article ID or a Model object
     * @param boolean $blnMultiMode   If true, only teasers will be shown
     * @param boolean $blnIsInsertTag If true, there will be no page relation
     * @param string  $strColumn      The name of the column
     *
     * @return string|boolean The article HTML markup or false
     */
    public function getArticle($varId, $blnMultiMode = false, $blnIsInsertTag = false, $strColumn = 'main');

    /**
     * Generate a content element and return it as string
     *
     * @param mixed  $intId     A content element ID or a Model object
     * @param string $strColumn The column the element is in
     *
     * @return string The content element HTML markup
     */
    public function getContentElement($intId, $strColumn = 'main');

    /**
     * Generate a form and return it as string
     *
     * @param mixed  $varId     A form ID or a Model object
     * @param string $strColumn The column the form is in
     *
     * @return string The form HTML markup
     */
    public function getForm($varId, $strColumn = 'main');

    /**
     * Calculate the page status icon name based on the page parameters
     *
     * @param object $objPage The page object
     *
     * @return string The status icon name
     */
    public function getPageStatusIcon($objPage);

    /**
     * Check whether an element is visible in the front end
     *
     * @param \Model|\ContentModel|\ModuleModel $objElement The element model
     *
     * @return boolean True if the element is visible
     */
    public function isVisibleElement($objElement);

    /**
     * Replace insert tags with their values
     *
     * @param string  $strBuffer The text with the tags to be replaced
     * @param boolean $blnCache  If false, non-cacheable tags will be replaced
     *
     * @return string The text with the replaced tags
     */
    public function replaceInsertTags($strBuffer, $blnCache = true);

    /**
     * Replace the dynamic script tags (see #4203)
     *
     * @param string $strBuffer The string with the tags to be replaced
     *
     * @return string The string with the replaced tags
     */
    public function replaceDynamicScriptTags($strBuffer);

    /**
     * Compile the margin format definition based on an array of values
     *
     * @param array  $arrValues An array of four values and a unit
     * @param string $strType   Either "margin" or "padding"
     *
     * @return string The CSS markup
     */
    public function generateMargin($arrValues, $strType = 'margin');

    /**
     * Reload the current page
     */
    public function reload();

    /**
     * Redirect to another page
     *
     * @param string  $strLocation The target URL
     * @param integer $intStatus   The HTTP status code (defaults to 303)
     */
    public function redirect($strLocation, $intStatus = 303);

    /**
     * Generate a front end URL
     *
     * @param array   $arrRow       An array of page parameters
     * @param string  $strParams    An optional string of URL parameters
     * @param string  $strForceLang Force a certain language
     * @param boolean $blnFixDomain Check the domain of the target page and append it if necessary
     *
     * @return string An URL that can be used in the front end
     */
    public function generateFrontendUrl($arrRow, $strParams = null, $strForceLang = null, $blnFixDomain = false);

    /**
     * Convert relative URLs in href and src attributes to absolute URLs
     *
     * @param string  $strContent  The text with the URLs to be converted
     * @param string  $strBase     An optional base URL
     * @param boolean $blnHrefOnly If true, only href attributes will be converted
     *
     * @return string The text with the replaced URLs
     */
    public function convertRelativeUrls($strContent, $strBase = '', $blnHrefOnly = false);

    /**
     * Send a file to the browser so the "save as …" dialogue opens
     *
     * @param string $strFile The file path
     */
    public function sendFileToBrowser($strFile);

    /**
     * Load a set of DCA files
     *
     * @param string  $strTable   The table name
     * @param boolean $blnNoCache If true, the cache will be bypassed
     */
    public function loadDataContainer($strTable, $blnNoCache = false);

    /**
     * Add an image to a template
     *
     * @param object  $objTemplate   The template object to add the image to
     * @param array   $arrItem       The element or module as array
     * @param integer $intMaxWidth   An optional maximum width of the image
     * @param string  $strLightboxId An optional lightbox ID
     */
    public function addImageToTemplate($objTemplate, $arrItem, $intMaxWidth = null, $strLightboxId = null);

    /**
     * Add enclosures to a template
     *
     * @param object $objTemplate The template object to add the enclosures to
     * @param array  $arrItem     The element or module as array
     * @param string $strKey      The name of the enclosures field in $arrItem
     */
    public function addEnclosuresToTemplate($objTemplate, $arrItem, $strKey = 'enclosure');

    /**
     * Set the static URL constants
     *
     * @param \PageModel $objPage An optional page object
     */
    public function setStaticUrls($objPage = null);

    /**
     * Add a static URL to a script
     *
     * @param string $script The script path
     *
     * @return string The script path with the static URL
     */
    public function addStaticUrlTo($script);

    /**
     * Return the back end themes as array
     *
     * @return array An array of available back end themes
     *
     * @deprecated Use Backend::getThemes() instead
     */
    public function getBackendThemes();

    /**
     * Get the details of a page including inherited parameters
     *
     * @param mixed $intId A page ID or a Model object
     *
     * @return \PageModel The page model or null
     *
     * @deprecated Use PageModel::findWithDetails() or PageModel->loadDetails() instead
     */
    public function getPageDetails($intId);

    /**
     * Restore basic entities
     *
     * @param string $strBuffer The string with the tags to be replaced
     *
     * @return string The string with the original entities
     *
     * @deprecated Use String::restoreBasicEntities() instead
     */
    public function restoreBasicEntities($strBuffer);

    /**
     * Generate an image tag and return it as string
     *
     * @param string $src        The image path
     * @param string $alt        An optional alt attribute
     * @param string $attributes A string of other attributes
     *
     * @return string The image HTML tag
     *
     * @deprecated Use Image::getHtml() instead
     */
    public function generateImage($src, $alt = '', $attributes = '');

    /**
     * Return all page sections as array
     *
     * @return array An array of active page sections
     *
     * @deprecated See #4693
     */
    public function getPageSections();

    /**
     * Return a "selected" attribute if the option is selected
     *
     * @param string $strOption The option to check
     * @param mixed  $varValues One or more values to check against
     *
     * @return string The attribute or an empty string
     *
     * @deprecated Use Widget::optionSelected() instead
     */
    public function optionSelected($strOption, $varValues);

    /**
     * Return a "checked" attribute if the option is checked
     *
     * @param string $strOption The option to check
     * @param mixed  $varValues One or more values to check against
     *
     * @return string The attribute or an empty string
     *
     * @deprecated Use Widget::optionChecked() instead
     */
    public function optionChecked($strOption, $varValues);

    /**
     * Find a content element in the TL_CTE array and return the class name
     *
     * @param string $strName The content element name
     *
     * @return string The class name
     *
     * @deprecated Use ContentElement::findClass() instead
     */
    public function findContentElement($strName);

    /**
     * Find a front end module in the FE_MOD array and return the class name
     *
     * @param string $strName The front end module name
     *
     * @return string The class name
     *
     * @deprecated Use Module::findClass() instead
     */
    public function findFrontendModule($strName);

    /**
     * Get an object property
     *
     * Lazy load the Input and Environment libraries (which are now static) and
     * only include them as object property if an old module requires it
     *
     * @param string $strKey The property name
     *
     * @return mixed|null The property value or null
     */
    public function getValue($strKey);

    /**
     * Import a library in non-object context
     *
     * @param string  $strClass The class name
     * @param string  $strKey   An optional key to store the object under
     * @param boolean $blnForce If true, existing objects will be overridden
     *
     * @return object The imported object
     */
    public function importStatic($strClass, $strKey = null, $blnForce = false);

    /**
     * Add a log entry to the database
     *
     * @param string $strText     The log message
     * @param string $strFunction The function name
     * @param string $strCategory The category name
     */
    public function log($strText, $strFunction, $strCategory);

    /**
     * Return the referer URL and optionally encode ampersands
     *
     * @param boolean $blnEncodeAmpersands If true, ampersands will be encoded
     * @param string  $strTable            An optional table name
     *
     * @return string The referer URL
     */
    public function getReferer($blnEncodeAmpersands = false, $strTable = null);

    /**
     * Load a set of language files
     *
     * @param string  $strName     The table name
     * @param boolean $strLanguage An optional language code
     * @param boolean $blnNoCache  If true, the cache will be bypassed
     */
    public function loadLanguageFile($strName, $strLanguage = null, $blnNoCache = false);

    /**
     * Check whether a language is installed
     *
     * @param boolean $strLanguage The language code
     *
     * @return boolean True if the language is installed
     */
    public function isInstalledLanguage($strLanguage);

    /**
     * Return the countries as array
     *
     * @return array An array of country names
     */
    public function getCountries();

    /**
     * Return the available languages as array
     *
     * @param boolean $blnInstalledOnly If true, return only installed languages
     *
     * @return array An array of languages
     */
    public function getLanguages($blnInstalledOnly = false);

    /**
     * Return the timezones as array
     *
     * @return array An array of timezones
     */
    public function getTimeZones();

    /**
     * Return all image sizes as array
     *
     * @return array The available image sizes
     */
    public function getImageSizes();

    /**
     * Urlencode a file path preserving slashes
     *
     * @param string $strPath The file path
     *
     * @return string The encoded file path
     */
    public function urlEncode($strPath);

    /**
     * Set a cookie
     *
     * @param string  $strName     The cookie name
     * @param mixed   $varValue    The cookie value
     * @param integer $intExpires  The expiration date
     * @param string  $strPath     An optional path
     * @param string  $strDomain   An optional domain name
     * @param boolean $blnSecure   If true, the secure flag will be set
     * @param boolean $blnHttpOnly If true, the http-only flag will be set
     */
    public function setCookie($strName, $varValue, $intExpires, $strPath = null, $strDomain = null, $blnSecure = false, $blnHttpOnly = false);

    /**
     * Convert a byte value into a human readable format
     *
     * @param integer $intSize     The size in bytes
     * @param integer $intDecimals The number of decimals to show
     *
     * @return string The human readable size
     */
    public function getReadableSize($intSize, $intDecimals = 1);

    /**
     * Format a number
     *
     * @param mixed   $varNumber   An integer or float number
     * @param integer $intDecimals The number of decimals to show
     *
     * @return mixed The formatted number
     */
    public function getFormattedNumber($varNumber, $intDecimals = 2);

    /**
     * Return the session hash
     *
     * @param string $strCookie The cookie name
     *
     * @return string The session hash
     */
    public function getSessionHash($strCookie);

    /**
     * Anonymize an IP address by overriding the last chunk
     *
     * @param string $strIp The IP address
     *
     * @return string The encoded IP address
     */
    public function anonymizeIp($strIp);

    /**
     * Convert an .xlf file into a PHP language file
     *
     * @param string  $strName     The name of the .xlf file
     * @param string  $strLanguage The language code
     * @param boolean $blnLoad     Add the labels to the global language array
     *
     * @return string The PHP code
     *
     * @deprecated Deprecated since version 4.0, to be removed in 5.0. Use the Contao\CoreBundle\Config\Loader\XliffFileLoader instead.
     */
    public function convertXlfToPhp($strName, $strLanguage, $blnLoad = false);

    /**
     * Parse a date format string and translate textual representations
     *
     * @param string  $strFormat The date format string
     * @param integer $intTstamp An optional timestamp
     *
     * @return string The textual representation of the date
     *
     * @deprecated Use Date::parse() instead
     */
    public function parseDate($strFormat, $intTstamp = null);

    /**
     * Split a friendly-name e-address and return name and e-mail as array
     *
     * @param string $strEmail A friendly-name e-mail address
     *
     * @return array An array with name and e-mail address
     *
     * @deprecated Use String::splitFriendlyEmail() instead
     */
    public function splitFriendlyName($strEmail);

    /**
     * Return the request string without the script name
     *
     * @param boolean $blnAmpersand If true, ampersands will be encoded
     *
     * @return string The request string
     *
     * @deprecated Use Environment::get('indexFreeRequest') instead
     */
    public function getIndexFreeRequest($blnAmpersand = true);

    /**
     * Compile a Model class name from a table name (e.g. tl_form_field becomes FormFieldModel)
     *
     * @param string $strTable The table name
     *
     * @return string The model class name
     *
     * @deprecated Use Model::getClassFromTable() instead
     */
    public function getModelClassFromTable($strTable);

    /**
     * Enable a back end module
     *
     * @param string $strName The module name
     *
     * @return boolean True if the module was enabled
     *
     * @deprecated Use Composer to add or remove modules
     */
    public function enableModule($strName);

    /**
     * Disable a back end module
     *
     * @param string $strName The module name
     *
     * @return boolean True if the module was disabled
     *
     * @deprecated Use Composer to add or remove modules
     */
    public function disableModule($strName);
}
