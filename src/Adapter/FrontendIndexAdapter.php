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
 * Provides an adapter for the Contao FrontendIndex class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FrontendIndexAdapter implements FrontendIndexAdapterInterface
{
    /**
     * Run the controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run()
    {
        $instance = new \Contao\FrontendIndex();
        return $instance->run();
    }

    /**
     * Split the current request into fragments, strip the URL suffix, recreate the $_GET array and return the page ID
     *
     * @return mixed
     */
    public function getPageIdFromUrl()
    {
        return \Contao\FrontendIndex::getPageIdFromUrl();
    }

    /**
     * Return the root page ID (backwards compatibility)
     *
     * @return integer
     */
    public function getRootIdFromUrl()
    {
        return \Contao\FrontendIndex::getRootIdFromUrl();
    }

    /**
     * Try to find a root page based on language and URL
     *
     * @return \PageModel
     */
    public function getRootPageFromUrl()
    {
        return \Contao\FrontendIndex::getRootPageFromUrl();
    }

    /**
     * Overwrite the parent method as front end URLs are handled differently
     *
     * @param string  $strRequest
     * @param boolean $blnIgnoreParams
     * @param array   $arrUnset
     *
     * @return string
     */
    public function addToUrl($strRequest, $blnIgnoreParams = false, $arrUnset = array())
    {
        return \Contao\FrontendIndex::addToUrl($strRequest, $blnIgnoreParams, $arrUnset);
    }

    /**
     * Get the meta data from a serialized string
     *
     * @param string $strData
     * @param string $strLanguage
     *
     * @return array
     */
    public function getMetaData($strData, $strLanguage)
    {
        return \Contao\FrontendIndex::getMetaData($strData, $strLanguage);
    }

    /**
     * Return the cron timeout in seconds
     *
     * @return integer
     */
    public function getCronTimeout()
    {
        return \Contao\FrontendIndex::getCronTimeout();
    }

    /**
     * Index a page if applicable
     *
     * @param Response $objResponse
     */
    public function indexPageIfApplicable($objResponse)
    {
        \Contao\FrontendIndex::indexPageIfApplicable($objResponse);
    }

    /**
     * Check whether there is a cached version of the page and return a response object
     * @return Response|null
     */
    public function getResponseFromCache()
    {
        return \Contao\FrontendIndex::getResponseFromCache();
    }

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
    public function getTemplate($strTemplate, $strFormat = 'html5')
    {
        return \Contao\FrontendIndex::getTemplate($strTemplate, $strFormat);
    }

    /**
     * Return all template files of a particular group as array
     *
     * @param string $strPrefix The template name prefix (e.g. "ce_")
     *
     * @return array An array of template names
     */
    public function getTemplateGroup($strPrefix)
    {
        return \Contao\FrontendIndex::getTemplateGroup($strPrefix);
    }

    /**
     * Generate a front end module and return it as string
     *
     * @param mixed  $intId     A module ID or a Model object
     * @param string $strColumn The name of the column
     *
     * @return string The module HTML markup
     */
    public function getFrontendModule($intId, $strColumn = 'main')
    {
        return \Contao\FrontendIndex::getFrontendModule($intId, $strColumn);
    }

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
    public function getArticle($varId, $blnMultiMode = false, $blnIsInsertTag = false, $strColumn = 'main')
    {
        return \Contao\FrontendIndex::getArticle($varId, $blnMultiMode, $blnIsInsertTag, $strColumn);
    }

    /**
     * Generate a content element and return it as string
     *
     * @param mixed  $intId     A content element ID or a Model object
     * @param string $strColumn The column the element is in
     *
     * @return string The content element HTML markup
     */
    public function getContentElement($intId, $strColumn = 'main')
    {
        return \Contao\FrontendIndex::getContentElement($intId, $strColumn);
    }

    /**
     * Generate a form and return it as string
     *
     * @param mixed  $varId     A form ID or a Model object
     * @param string $strColumn The column the form is in
     *
     * @return string The form HTML markup
     */
    public function getForm($varId, $strColumn = 'main')
    {
        return \Contao\FrontendIndex::getForm($varId, $strColumn);
    }

    /**
     * Calculate the page status icon name based on the page parameters
     *
     * @param object $objPage The page object
     *
     * @return string The status icon name
     */
    public function getPageStatusIcon($objPage)
    {
        return \Contao\FrontendIndex::getPageStatusIcon($objPage);
    }

    /**
     * Check whether an element is visible in the front end
     *
     * @param \Model|\ContentModel|\ModuleModel $objElement The element model
     *
     * @return boolean True if the element is visible
     */
    public function isVisibleElement($objElement)
    {
        return \Contao\FrontendIndex::isVisibleElement($objElement);
    }

    /**
     * Replace insert tags with their values
     *
     * @param string  $strBuffer The text with the tags to be replaced
     * @param boolean $blnCache  If false, non-cacheable tags will be replaced
     *
     * @return string The text with the replaced tags
     */
    public function replaceInsertTags($strBuffer, $blnCache = true)
    {
        return \Contao\FrontendIndex::replaceInsertTags($strBuffer, $blnCache);
    }

    /**
     * Replace the dynamic script tags (see #4203)
     *
     * @param string $strBuffer The string with the tags to be replaced
     *
     * @return string The string with the replaced tags
     */
    public function replaceDynamicScriptTags($strBuffer)
    {
        return \Contao\FrontendIndex::replaceDynamicScriptTags($strBuffer);
    }

    /**
     * Compile the margin format definition based on an array of values
     *
     * @param array  $arrValues An array of four values and a unit
     * @param string $strType   Either "margin" or "padding"
     *
     * @return string The CSS markup
     */
    public function generateMargin($arrValues, $strType = 'margin')
    {
        return \Contao\FrontendIndex::generateMargin($arrValues, $strType);
    }

    /**
     * Reload the current page
     */
    public function reload()
    {
        \Contao\FrontendIndex::reload();
    }

    /**
     * Redirect to another page
     *
     * @param string  $strLocation The target URL
     * @param integer $intStatus   The HTTP status code (defaults to 303)
     */
    public function redirect($strLocation, $intStatus = 303)
    {
        \Contao\FrontendIndex::redirect($strLocation, $intStatus);
    }

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
    public function generateFrontendUrl($arrRow, $strParams = null, $strForceLang = null, $blnFixDomain = false)
    {
        return \Contao\FrontendIndex::generateFrontendUrl($arrRow, $strParams, $strForceLang, $blnFixDomain);
    }

    /**
     * Convert relative URLs in href and src attributes to absolute URLs
     *
     * @param string  $strContent  The text with the URLs to be converted
     * @param string  $strBase     An optional base URL
     * @param boolean $blnHrefOnly If true, only href attributes will be converted
     *
     * @return string The text with the replaced URLs
     */
    public function convertRelativeUrls($strContent, $strBase = '', $blnHrefOnly = false)
    {
        return \Contao\FrontendIndex::convertRelativeUrls($strContent, $strBase, $blnHrefOnly);
    }

    /**
     * Send a file to the browser so the "save as …" dialogue opens
     *
     * @param string $strFile The file path
     */
    public function sendFileToBrowser($strFile)
    {
        \Contao\FrontendIndex::sendFileToBrowser($strFile);
    }

    /**
     * Load a set of DCA files
     *
     * @param string  $strTable   The table name
     * @param boolean $blnNoCache If true, the cache will be bypassed
     */
    public function loadDataContainer($strTable, $blnNoCache = false)
    {
        \Contao\FrontendIndex::loadDataContainer($strTable, $blnNoCache);
    }

    /**
     * Add an image to a template
     *
     * @param object  $objTemplate   The template object to add the image to
     * @param array   $arrItem       The element or module as array
     * @param integer $intMaxWidth   An optional maximum width of the image
     * @param string  $strLightboxId An optional lightbox ID
     */
    public function addImageToTemplate($objTemplate, $arrItem, $intMaxWidth = null, $strLightboxId = null)
    {
        \Contao\FrontendIndex::addImageToTemplate($objTemplate, $arrItem, $intMaxWidth, $strLightboxId);
    }

    /**
     * Add enclosures to a template
     *
     * @param object $objTemplate The template object to add the enclosures to
     * @param array  $arrItem     The element or module as array
     * @param string $strKey      The name of the enclosures field in $arrItem
     */
    public function addEnclosuresToTemplate($objTemplate, $arrItem, $strKey = 'enclosure')
    {
        \Contao\FrontendIndex::addEnclosuresToTemplate($objTemplate, $arrItem, $strKey);
    }

    /**
     * Set the static URL constants
     *
     * @param \PageModel $objPage An optional page object
     */
    public function setStaticUrls($objPage = null)
    {
        \Contao\FrontendIndex::setStaticUrls($objPage);
    }

    /**
     * Add a static URL to a script
     *
     * @param string $script The script path
     *
     * @return string The script path with the static URL
     */
    public function addStaticUrlTo($script)
    {
        return \Contao\FrontendIndex::addStaticUrlTo($script);
    }

    /**
     * Return the current theme as string
     *
     * @return string The name of the theme
     *
     * @deprecated Use Backend::getTheme() instead
     */
    public function getTheme()
    {
        return \Contao\FrontendIndex::getTheme();
    }

    /**
     * Return the back end themes as array
     *
     * @return array An array of available back end themes
     *
     * @deprecated Use Backend::getThemes() instead
     */
    public function getBackendThemes()
    {
        return \Contao\FrontendIndex::getBackendThemes();
    }

    /**
     * Get the details of a page including inherited parameters
     *
     * @param mixed $intId A page ID or a Model object
     *
     * @return \PageModel The page model or null
     *
     * @deprecated Use PageModel::findWithDetails() or PageModel->loadDetails() instead
     */
    public function getPageDetails($intId)
    {
        return \Contao\FrontendIndex::getPageDetails($intId);
    }

    /**
     * Restore basic entities
     *
     * @param string $strBuffer The string with the tags to be replaced
     *
     * @return string The string with the original entities
     *
     * @deprecated Use String::restoreBasicEntities() instead
     */
    public function restoreBasicEntities($strBuffer)
    {
        return \Contao\FrontendIndex::restoreBasicEntities($strBuffer);
    }

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
    public function generateImage($src, $alt = '', $attributes = '')
    {
        return \Contao\FrontendIndex::generateImage($src, $alt, $attributes);
    }

    /**
     * Return all page sections as array
     *
     * @return array An array of active page sections
     *
     * @deprecated See #4693
     */
    public function getPageSections()
    {
        return \Contao\FrontendIndex::getPageSections();
    }

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
    public function optionSelected($strOption, $varValues)
    {
        return \Contao\FrontendIndex::optionSelected($strOption, $varValues);
    }

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
    public function optionChecked($strOption, $varValues)
    {
        return \Contao\FrontendIndex::optionChecked($strOption, $varValues);
    }

    /**
     * Find a content element in the TL_CTE array and return the class name
     *
     * @param string $strName The content element name
     *
     * @return string The class name
     *
     * @deprecated Use ContentElement::findClass() instead
     */
    public function findContentElement($strName)
    {
        return \Contao\FrontendIndex::findContentElement($strName);
    }

    /**
     * Find a front end module in the FE_MOD array and return the class name
     *
     * @param string $strName The front end module name
     *
     * @return string The class name
     *
     * @deprecated Use Module::findClass() instead
     */
    public function findFrontendModule($strName)
    {
        return \Contao\FrontendIndex::findFrontendModule($strName);
    }

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
    public function getValue($strKey)
    {
        $instance = new \Contao\FrontendIndex();
        return $instance->{$strKey};
    }

    /**
     * Import a library in non-object context
     *
     * @param string  $strClass The class name
     * @param string  $strKey   An optional key to store the object under
     * @param boolean $blnForce If true, existing objects will be overridden
     *
     * @return object The imported object
     */
    public function importStatic($strClass, $strKey = null, $blnForce = false)
    {
        return \Contao\FrontendIndex::importStatic($strClass, $strKey, $blnForce);
    }

    /**
     * Add a log entry to the database
     *
     * @param string $strText     The log message
     * @param string $strFunction The function name
     * @param string $strCategory The category name
     */
    public function log($strText, $strFunction, $strCategory)
    {
        \Contao\FrontendIndex::log($strText, $strFunction, $strCategory);
    }

    /**
     * Return the referer URL and optionally encode ampersands
     *
     * @param boolean $blnEncodeAmpersands If true, ampersands will be encoded
     * @param string  $strTable            An optional table name
     *
     * @return string The referer URL
     */
    public function getReferer($blnEncodeAmpersands = false, $strTable = null)
    {
        return \Contao\FrontendIndex::getReferer($blnEncodeAmpersands, $strTable);
    }

    /**
     * Load a set of language files
     *
     * @param string  $strName     The table name
     * @param boolean $strLanguage An optional language code
     * @param boolean $blnNoCache  If true, the cache will be bypassed
     */
    public function loadLanguageFile($strName, $strLanguage = null, $blnNoCache = false)
    {
        \Contao\FrontendIndex::loadLanguageFile($strName, $strLanguage, $blnNoCache);
    }

    /**
     * Check whether a language is installed
     *
     * @param boolean $strLanguage The language code
     *
     * @return boolean True if the language is installed
     */
    public function isInstalledLanguage($strLanguage)
    {
        return \Contao\FrontendIndex::isInstalledLanguage($strLanguage);
    }

    /**
     * Return the countries as array
     *
     * @return array An array of country names
     */
    public function getCountries()
    {
        return \Contao\FrontendIndex::getCountries();
    }

    /**
     * Return the available languages as array
     *
     * @param boolean $blnInstalledOnly If true, return only installed languages
     *
     * @return array An array of languages
     */
    public function getLanguages($blnInstalledOnly = false)
    {
        return \Contao\FrontendIndex::getLanguages($blnInstalledOnly);
    }

    /**
     * Return the timezones as array
     *
     * @return array An array of timezones
     */
    public function getTimeZones()
    {
        return \Contao\FrontendIndex::getTimeZones();
    }

    /**
     * Return all image sizes as array
     *
     * @return array The available image sizes
     */
    public function getImageSizes()
    {
        return \Contao\FrontendIndex::getImageSizes();
    }

    /**
     * Urlencode a file path preserving slashes
     *
     * @param string $strPath The file path
     *
     * @return string The encoded file path
     */
    public function urlEncode($strPath)
    {
        return \Contao\FrontendIndex::urlEncode($strPath);
    }

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
    public function setCookie($strName, $varValue, $intExpires, $strPath = null, $strDomain = null, $blnSecure = false, $blnHttpOnly = false)
    {
        \Contao\FrontendIndex::setCookie($strName, $varValue, $intExpires, $strPath, $strDomain, $blnSecure, $blnHttpOnly);
    }

    /**
     * Convert a byte value into a human readable format
     *
     * @param integer $intSize     The size in bytes
     * @param integer $intDecimals The number of decimals to show
     *
     * @return string The human readable size
     */
    public function getReadableSize($intSize, $intDecimals = 1)
    {
        return \Contao\FrontendIndex::getReadableSize($intSize, $intDecimals);
    }

    /**
     * Format a number
     *
     * @param mixed   $varNumber   An integer or float number
     * @param integer $intDecimals The number of decimals to show
     *
     * @return mixed The formatted number
     */
    public function getFormattedNumber($varNumber, $intDecimals = 2)
    {
        return \Contao\FrontendIndex::getFormattedNumber($varNumber, $intDecimals);
    }

    /**
     * Return the session hash
     *
     * @param string $strCookie The cookie name
     *
     * @return string The session hash
     */
    public function getSessionHash($strCookie)
    {
        return \Contao\FrontendIndex::getSessionHash($strCookie);
    }

    /**
     * Anonymize an IP address by overriding the last chunk
     *
     * @param string $strIp The IP address
     *
     * @return string The encoded IP address
     */
    public function anonymizeIp($strIp)
    {
        return \Contao\FrontendIndex::anonymizeIp($strIp);
    }

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
    public function convertXlfToPhp($strName, $strLanguage, $blnLoad = false)
    {
        return \Contao\FrontendIndex::convertXlfToPhp($strName, $strLanguage, $blnLoad);
    }

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
    public function parseDate($strFormat, $intTstamp = null)
    {
        return \Contao\FrontendIndex::parseDate($strFormat, $intTstamp);
    }

    /**
     * Split a friendly-name e-address and return name and e-mail as array
     *
     * @param string $strEmail A friendly-name e-mail address
     *
     * @return array An array with name and e-mail address
     *
     * @deprecated Use String::splitFriendlyEmail() instead
     */
    public function splitFriendlyName($strEmail)
    {
        return \Contao\FrontendIndex::splitFriendlyName($strEmail);
    }

    /**
     * Return the request string without the script name
     *
     * @param boolean $blnAmpersand If true, ampersands will be encoded
     *
     * @return string The request string
     *
     * @deprecated Use Environment::get('indexFreeRequest') instead
     */
    public function getIndexFreeRequest($blnAmpersand = true)
    {
        return \Contao\FrontendIndex::getIndexFreeRequest($blnAmpersand);
    }

    /**
     * Compile a Model class name from a table name (e.g. tl_form_field becomes FormFieldModel)
     *
     * @param string $strTable The table name
     *
     * @return string The model class name
     *
     * @deprecated Use Model::getClassFromTable() instead
     */
    public function getModelClassFromTable($strTable)
    {
        return \Contao\FrontendIndex::getModelClassFromTable($strTable);
    }

    /**
     * Enable a back end module
     *
     * @param string $strName The module name
     *
     * @return boolean True if the module was enabled
     *
     * @deprecated Use Composer to add or remove modules
     */
    public function enableModule($strName)
    {
        \Contao\FrontendIndex::enableModule($strName);
    }

    /**
     * Disable a back end module
     *
     * @param string $strName The module name
     *
     * @return boolean True if the module was disabled
     *
     * @deprecated Use Composer to add or remove modules
     */
    public function disableModule($strName)
    {
        \Contao\FrontendIndex::disableModule($strName);
    }
}
