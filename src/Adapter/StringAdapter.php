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
 * Provides an adapter for the Contao String class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class StringAdapter implements StringAdapterInterface
{
    /**
     * Shorten a string to a given number of characters
     *
     * The function preserves words, so the result might be a bit shorter or
     * longer than the number of characters given. It strips all tags.
     *
     * @param string  $strString        The string to shorten
     * @param integer $intNumberOfChars The target number of characters
     * @param string  $strEllipsis      An optional ellipsis to append to the shortened string
     *
     * @return string The shortened string
     */
    public function substr($strString, $intNumberOfChars, $strEllipsis = '…')
    {
        return \Contao\String::substr($strString, $intNumberOfChars, $strEllipsis);
    }

    /**
     * Shorten a HTML string to a given number of characters
     *
     * The function preserves words, so the result might be a bit shorter or
     * longer than the number of characters given. It preserves allowed tags.
     *
     * @param string  $strString        The string to shorten
     * @param integer $intNumberOfChars The target number of characters
     *
     * @return string The shortened HTML string
     */
    public function substrHtml($strString, $intNumberOfChars)
    {
        return \Contao\String::substrHtml($strString, $intNumberOfChars);
    }

    /**
     * Decode all entities
     *
     * @param string  $strString     The string to decode
     * @param integer $strQuoteStyle The quote style (defaults to ENT_COMPAT)
     * @param string  $strCharset    An optional charset
     *
     * @return string The decoded string
     */
    public function decodeEntities($strString, $strQuoteStyle = 2, $strCharset = null)
    {
        return \Contao\String::decodeEntities($strString, $strQuoteStyle, $strCharset);
    }

    /**
     * Restore basic entities
     *
     * @param string $strBuffer The string with the tags to be replaced
     *
     * @return string The string with the original entities
     */
    public function restoreBasicEntities($strBuffer)
    {
        return \Contao\String::restoreBasicEntities($strBuffer);
    }

    /**
     * Censor a single word or an array of words within a string
     *
     * @param string $strString  The string to censor
     * @param mixed  $varWords   A string or array or words to replace
     * @param string $strReplace An optional replacement string
     *
     * @return string The cleaned string
     */
    public function censor($strString, $varWords, $strReplace = '')
    {
        return \Contao\String::censor($strString, $varWords, $strReplace);
    }

    /**
     * Encode all e-mail addresses within a string
     *
     * @param string $strString The string to encode
     *
     * @return string The encoded string
     */
    public function encodeEmail($strString)
    {
        return \Contao\String::encodeEmail($strString);
    }

    /**
     * Split a friendly-name e-address and return name and e-mail as array
     *
     * @param string $strEmail A friendly-name e-mail address
     *
     * @return array An array with name and e-mail address
     */
    public function splitFriendlyEmail($strEmail)
    {
        return \Contao\String::splitFriendlyEmail($strEmail);
    }

    /**
     * Wrap words after a particular number of characers
     *
     * @param string  $strString The string to wrap
     * @param integer $strLength The number of characters to wrap after
     * @param string  $strBreak  An optional break character
     *
     * @return string The wrapped string
     */
    public function wordWrap($strString, $strLength = 75, $strBreak = '')
    {
        return \Contao\String::wordWrap($strString, $strLength, $strBreak);
    }

    /**
     * Highlight a phrase within a string
     *
     * @param string $strString     The string
     * @param string $strPhrase     The phrase to highlight
     * @param string $strOpeningTag The opening tag (defaults to <strong>)
     * @param string $strClosingTag The closing tag (defaults to </strong>)
     *
     * @return string The highlighted string
     */
    public function highlight($strString, $strPhrase, $strOpeningTag = '<strong>', $strClosingTag = '</strong>')
    {
        return \Contao\String::highlight($strString, $strPhrase, $strOpeningTag, $strClosingTag);
    }

    /**
     * Split a string of comma separated values
     *
     * @param string $strString    The string to split
     * @param string $strDelimiter An optional delimiter
     *
     * @return array The string chunks
     */
    public function splitCsv($strString, $strDelimiter = ',')
    {
        return \Contao\String::splitCsv($strString, $strDelimiter);
    }

    /**
     * Convert a string to XHTML
     *
     * @param string $strString The HTML5 string
     *
     * @return string The XHTML string
     */
    public function toXhtml($strString)
    {
        return \Contao\String::toXhtml($strString);
    }

    /**
     * Convert a string to HTML5
     *
     * @param string $strString The XHTML string
     *
     * @return string The HTML5 string
     */
    public function toHtml5($strString)
    {
        return \Contao\String::toHtml5($strString);
    }

    /**
     * Parse simple tokens that can be used to personalize newsletters
     *
     * @param string $strString The string to be parsed
     * @param array  $arrData   The replacement data
     *
     * @return string The converted string
     *
     * @throws \Exception If $strString cannot be parsed
     */
    public function parseSimpleTokens($strString, $arrData)
    {
        return \Contao\String::parseSimpleTokens($strString, $arrData);
    }

    /**
     * Convert a UUID string to binary data
     *
     * @param string $uuid The UUID string
     *
     * @return string The binary data
     */
    public function uuidToBin($uuid)
    {
        return \Contao\String::uuidToBin($uuid);
    }

    /**
     * Get a UUID string from binary data
     *
     * @param string $data The binary data
     *
     * @return string The UUID string
     */
    public function binToUuid($data)
    {
        return \Contao\String::binToUuid($data);
    }

    /**
     * Convert file paths inside "src" attributes to insert tags
     *
     * @param string $data The markup string
     *
     * @return string The markup with file paths converted to insert tags
     */
    public function srcToInsertTag($data)
    {
        return \Contao\String::srcToInsertTag($data);
    }

    /**
     * Convert insert tags inside "src" attributes to file paths
     *
     * @param string $data The markup string
     *
     * @return string The markup with insert tags converted to file paths
     */
    public function insertTagToSrc($data)
    {
        return \Contao\String::insertTagToSrc($data);
    }

    /**
     * Resolve a flagged URL such as assets/js/core.js|static|10184084
     *
     * @param string $url The URL
     *
     * @return \stdClass The options object
     */
    public function resolveFlaggedUrl($url)
    {
        return \Contao\String::resolveFlaggedUrl($url);
    }

    /**
     * Return the object instance (Singleton)
     *
     * @return \String The object instance
     *
     * @deprecated String is now a static class
     */
    public function instantiate()
    {
        return \Contao\String::getInstance();
    }
}
