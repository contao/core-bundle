<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

/**
 * Show a special Contao "what to do in case of an error" message
 *
 * @throws \Contao\CoreBundle\Exception\DieNicelyException
 *
 * @deprecated Deprecated since Contao 4.0 to be removed in Contao 5.0 - throw a DieNicelyException instead.
 *
 * @see \Contao\CoreBundle\Exception\DieNicelyException
 */
function show_help_message()
{
	throw new \Contao\CoreBundle\Exception\DieNicelyException(
		'be_error',
		'An error occurred while executing this script!'
	);
}


/**
 * Try to die with a template instead of just a message
 * @param string
 * @param string
 *
 * @throws \Contao\CoreBundle\Exception\DieNicelyException
 *
 * @deprecated Deprecated since Contao 4.0 to be removed in Contao 5.0 - throw a DieNicelyException instead.
 *
 * @see \Contao\CoreBundle\Exception\DieNicelyException
 */
function die_nicely($strTemplate, $strFallback)
{
	throw new \Contao\CoreBundle\Exception\DieNicelyException($strTemplate, $strFallback);
}


/**
 * Add a log entry
 * @param string
 * @param string
 */
function log_message($strMessage, $strLog='error.log')
{
    /** @var \Contao\CoreBundle\HttpKernel\ContaoKernel $kernel */
    global $kernel;
    // FIXME: This needs to be rewritten.
	@error_log(sprintf("[%s] %s\n", date('d-M-Y H:i:s'), $strMessage), 3, TL_ROOT . '/system/logs/' . $strLog);
    if ($strLog !== 'error.log') {
        $logFile = new \Monolog\Handler\RotatingFileHandler(sprintf('%1$s/logs/%2$s', $kernel->getRootDir(), $strLog), 4);
        $logFile->handle()
    }

}


/**
 * Scan a directory and return its files and folders as array
 * @param string
 * @param bool
 * @return array
 */
function scan($strFolder, $blnUncached=false)
{
	global $arrScanCache;

	// Add a trailing slash
	if (substr($strFolder, -1, 1) != '/')
	{
		$strFolder .= '/';
	}

	// Load from cache
	if (!$blnUncached && isset($arrScanCache[$strFolder]))
	{
		return $arrScanCache[$strFolder];
	}

	$arrReturn = [];

	// Scan directory
	foreach (scandir($strFolder) as $strFile)
	{
		if ($strFile == '.' || $strFile == '..')
		{
			continue;
		}

		$arrReturn[] = $strFile;
	}

	// Cache the result
	if (!$blnUncached)
	{
		$arrScanCache[$strFolder] = $arrReturn;
	}

	return $arrReturn;
}


/**
 * Convert special characters to HTML entities and make sure that
 * entities are never double converted.
 * @param string
 * @param bool
 * @return string
 */
function specialchars($strString, $blnStripInsertTags=false)
{
	if ($blnStripInsertTags)
	{
		$strString = strip_insert_tags($strString);
	}

	// Use ENT_COMPAT here (see #4889)
	return htmlspecialchars($strString, ENT_COMPAT, $GLOBALS['TL_CONFIG']['characterSet'], false);
}


/**
 * Standardize a parameter (strip special characters and convert spaces)
 * @param string
 * @param bool
 * @return string
 */
function standardize($strString, $blnPreserveUppercase=false)
{
	$arrSearch = ['/[^a-zA-Z0-9 \.\&\/_-]+/', '/[ \.\&\/-]+/'];
	$arrReplace = ['', '-'];

	$strString = html_entity_decode($strString, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
	$strString = strip_insert_tags($strString);
	$strString = utf8_romanize($strString);
	$strString = preg_replace($arrSearch, $arrReplace, $strString);

	if (is_numeric(substr($strString, 0, 1)))
	{
		$strString = 'id-' . $strString;
	}

	if (!$blnPreserveUppercase)
	{
		$strString = strtolower($strString);
	}

	return trim($strString, '-');
}


/**
 * Remove Contao insert tags from a string
 * @param string
 * @return string
 */
function strip_insert_tags($strString)
{
	return preg_replace('/\{\{[^\}]+\}\}/U', '', $strString);
}


/**
 * Return an unserialized array or the argument
 * @param mixed
 * @param bool
 * @return mixed
 */
function deserialize($varValue, $blnForceArray=false)
{
	// Already an array
	if (is_array($varValue))
	{
		return $varValue;
	}

	// Null
	if ($varValue === null)
	{
		return $blnForceArray ? [] : null;
	}

	// Not a string
	if (!is_string($varValue))
	{
		return $blnForceArray ? [$varValue] : $varValue;
	}

	// Empty string
	if (trim($varValue) == '')
	{
		return $blnForceArray ? [] : '';
	}

	// Potentially including an object (see #6724)
	if (preg_match('/[OoC]:\+?[0-9]+:"/', $varValue))
	{
		trigger_error('The deserialize() function does not allow serialized objects', E_USER_WARNING);
		return $blnForceArray ? [$varValue] : $varValue;
	}

	$varUnserialized = @unserialize($varValue);

	if (is_array($varUnserialized))
	{
		$varValue = $varUnserialized;
	}
	elseif ($blnForceArray)
	{
		$varValue = [$varValue];
	}

	return $varValue;
}


/**
 * Split a string into fragments, remove whitespace and return fragments as array
 * @param string
 * @param string
 * @return array
 */
function trimsplit($strPattern, $strString)
{
	global $arrSplitCache;
	$strKey = md5($strPattern.$strString);

	// Load from cache
	if (isset($arrSplitCache[$strKey]))
	{
		return $arrSplitCache[$strKey];
	}

	// Split
	if (strlen($strPattern) == 1)
	{
		$arrFragments = array_map('trim', explode($strPattern, $strString));
	}
	else
	{
		$arrFragments = array_map('trim', preg_split('/'.$strPattern.'/ui', $strString));
	}

	// Empty array
	if (count($arrFragments) < 2 && !strlen($arrFragments[0]))
	{
		$arrFragments = [];
	}

	$arrSplitCache[$strKey] = $arrFragments;
	return $arrFragments;
}


/**
 * Convert all ampersands into their HTML entity (default) or unencoded value
 * @param string
 * @param bool
 * @return string
 */
function ampersand($strString, $blnEncode=true)
{
	return preg_replace('/&(amp;)?/i', ($blnEncode ? '&amp;' : '&'), $strString);
}


/**
 * Replace line breaks with HTML5-style <br> tags
 * @param string
 * @param bool
 * @return string
 */
function nl2br_html5($str, $xhtml=false)
{
	return nl2br($str, $xhtml);
}


/**
 * Replace line breaks with XHTML-style <br /> tags
 * @param string
 * @return string
 */
function nl2br_xhtml($str)
{
	return nl2br($str);
}


/**
 * Replace line breaks with <br> tags preserving preformatted text
 * @param string
 * @param bool
 * @return string
 */
function nl2br_pre($str, $xhtml=false)
{
	$str = $xhtml ? nl2br_xhtml($str) : nl2br_html5($str);

	if (stripos($str, '<pre') === false)
	{
		return $str;
	}

	$chunks = [];
	preg_match_all('/<pre[^>]*>.*<\/pre>/Uis', $str, $chunks);

	foreach ($chunks as $chunk)
	{
		$str = str_replace($chunk, str_ireplace(['<br>', '<br />'], '', $chunk), $str);
	}

	return $str;
}


/**
 * Dump variables depending on their type either with var_dump() or print_r()
 */
function dump()
{
	echo "<pre>";

	foreach (func_get_args() as $var)
	{
		if (is_array($var) || is_object($var))
		{
			print_r($var);
		}
		else
		{
			var_dump($var);
		}
	}

	echo "</pre>";
}


/**
 * Compare two file names using a case insensitive "natural order" algorithm
 * @param string
 * @param string
 * @return int
 */
function basename_natcasecmp($a, $b)
{
	return strnatcasecmp(basename($a), basename($b));
}


/**
 * Compare two file names using a case insensitive, reverse "natural order" algorithm
 * @param string
 * @param string
 * @return int
 */
function basename_natcasercmp($a, $b)
{
	return - strnatcasecmp(basename($a), basename($b));
}


/**
 * Sort an array by keys using a case insensitive "natural order" algorithm
 * @param array
 * @return array
 */
function natcaseksort($arrArray)
{
	$arrBuffer = array_flip($arrArray);
	natcasesort($arrBuffer);
	$arrBuffer = array_flip($arrBuffer);

	return $arrBuffer;
}


/**
 * Compare two values based on their length (ascending)
 * @param int
 * @param int
 * @return int
 */
function length_sort_asc($a, $b)
{
	return strlen($a) - strlen($b);
}


/**
 * Compare two values based on their length (descending)
 * @param int
 * @param int
 * @return int
 */
function length_sort_desc($a, $b)
{
	return strlen($b) - strlen($a);
}


/**
 * Insert a parameter or array into an existing array at a particular index
 * @param array
 * @param int
 * @param mixed
 */
function array_insert(&$arrCurrent, $intIndex, $arrNew)
{
	if (!is_array($arrCurrent))
	{
		$arrCurrent = $arrNew;
		return;
	}

	if (is_array($arrNew))
	{
		$arrBuffer = array_splice($arrCurrent, 0, $intIndex);
		$arrCurrent = array_merge_recursive($arrBuffer, $arrNew, $arrCurrent);
		return;
	}

	array_splice($arrCurrent, $intIndex, 0, $arrNew);
}


/**
 * Duplicate a particular element of an array
 * @param array
 * @param int
 * @return array
 */
function array_duplicate($arrStack, $intIndex)
{
	$arrBuffer = $arrStack;
	$arrStack = [];

	for ($i=0; $i<=$intIndex; $i++)
	{
		$arrStack[] = $arrBuffer[$i];
	}

	for ($i=$intIndex, $c=count($arrBuffer); $i<$c; $i++)
	{
		$arrStack[] = $arrBuffer[$i];
	}

	return $arrStack;
}


/**
 * Move an array element one position up
 * @param array
 * @param int
 * @return array
 */
function array_move_up($arrStack, $intIndex)
{
	if ($intIndex > 0)
	{
		$arrBuffer = $arrStack[$intIndex];
		$arrStack[$intIndex] = $arrStack[($intIndex-1)];
		$arrStack[($intIndex-1)] = $arrBuffer;
	}
	else
	{
		array_push($arrStack, $arrStack[$intIndex]);
		array_shift($arrStack);
	}

	return $arrStack;
}


/**
 * Move an array element one position down
 * @param array
 * @param int
 * @return array
 */
function array_move_down($arrStack, $intIndex)
{
	if (($intIndex+1) < count($arrStack))
	{
		$arrBuffer = $arrStack[$intIndex];
		$arrStack[$intIndex] = $arrStack[($intIndex+1)];
		$arrStack[($intIndex+1)] = $arrBuffer;
	}
	else
	{
		array_unshift($arrStack, $arrStack[$intIndex]);
		array_pop($arrStack);
	}

	return $arrStack;
}


/**
 * Delete a particular element of an array
 * @param array
 * @param int
 * @return array
 */
function array_delete($arrStack, $intIndex)
{
	unset($arrStack[$intIndex]);
	return array_values($arrStack);
}


/**
 * Return true if an array is associative
 * @param array
 * @return bool
 */
function array_is_assoc($arrArray)
{
	return (is_array($arrArray) && array_keys($arrArray) !== range(0, (sizeof($arrArray) - 1)));
}


/**
 * Load the mbstring library
 */
require __DIR__ . '/mbstring.php';


/**
 * Define some mbstring wrapper functions
 */
if (!USE_MBSTRING)
{
	/**
	 * Convert character encoding
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	function mb_convert_encoding($str, $to, $from=null)
	{
		if ($from === null)
			return utf8_convert_encoding($str, $to);

		return utf8_convert_encoding($str, $to, $from);
	}

	/**
	 * Detect the encoding of a string
	 * @param string
	 * @return string
	 */
	function mb_detect_encoding($str)
	{
		return utf8_detect_encoding($str);
	}

	/**
	 * Find the last occurrence of a character in a string (case-insensitive)
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	function mb_stripos($haystack, $needle, $offset=null)
	{
		if ($offset === null)
			return stripos($haystack, $needle);

		return stripos($haystack, $needle, $offset);
	}

	/**
	 * Find the first occurrence of a character in a string (case-insensitive)
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	function mb_stristr($haystack, $needle)
	{
		return stristr($haystack, $needle);
	}

	/**
	 * Determine the number of characters of a string
	 * @param string
	 * @return int
	 */
	function mb_strlen($str)
	{
		return utf8_strlen($str);
	}

	/**
	 * Find the first occurrence of a character in a string
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	function mb_strpos($haystack, $needle, $offset=0)
	{
		if ($offset === 0)
			return utf8_strpos($haystack, $needle);

		return utf8_strpos($haystack, $needle, $offset);
	}

	/**
	 * Find the last occurrence of a character in a string
	 * @param string
	 * @param string
	 * @return string
	 */
	function mb_strrchr($haystack, $needle)
	{
		return utf8_strrchr($haystack, $needle);
	}

	/**
	 * Find the position of the last occurrence of a string in another string
	 * @param string
	 * @param string
	 * @return mixed
	 */
	function mb_strrpos($haystack, $needle)
	{
		return utf8_strrpos($haystack, $needle);
	}

	/**
	 * Find the first occurrence of a string in another string
	 * @param string
	 * @param string
	 * @return string
	 */
	function mb_strstr($haystack, $needle)
	{
		return utf8_strstr($haystack, $needle);
	}

	/**
	 * Make a string lowercase
	 * @param string
	 * @return string
	 */
	function mb_strtolower($str)
	{
		return utf8_strtolower($str);
	}

	/**
	 * Make a string uppercase
	 * @param string
	 * @return string
	 */
	function mb_strtoupper($str)
	{
		return utf8_strtoupper($str);
	}

	/**
	 * Return a substring of a string
	 * @param string
	 * @param int
	 * @param int
	 * @return string
	 */
	function mb_substr($str, $start, $length=null)
	{
		if ($length === null)
			return utf8_substr($str, $start);

		return utf8_substr($str, $start, $length);
	}

	/**
	 * Count the number of substring occurrences
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	function mb_substr_count($haystack, $needle, $offset=null)
	{
		if ($offset === null)
			return substr_count($haystack, $needle);

		return substr_count($haystack, $needle, $offset);
	}
}


/**
 * Replace line breaks with <br> tags (to be used with preg_replace_callback)
 * @param array
 * @return string
 * @deprecated
 */
function nl2br_callback($matches)
{
	return str_replace("\n", '<br>', $matches[0]);
}
