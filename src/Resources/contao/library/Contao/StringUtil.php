<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Patchwork\Utf8;
use Psr\Log\LogLevel;

/**
 * Provides string manipulation methods
 *
 * Usage:
 *
 *     $short = StringUtil::substr($str, 32);
 *     $html  = StringUtil::substrHtml($str, 32);
 *     $xhtml = StringUtil::toXhtml($html5);
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class StringUtil
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
	public static function substr($strString, $intNumberOfChars, $strEllipsis=' …')
	{
		$strString = preg_replace('/[\t\n\r]+/', ' ', $strString);
		$strString = strip_tags($strString);

		if (Utf8::strlen($strString) <= $intNumberOfChars)
		{
			return $strString;
		}

		$intCharCount = 0;
		$arrWords = array();
		$arrChunks = preg_split('/\s+/', $strString);
		$blnAddEllipsis = false;

		foreach ($arrChunks as $strChunk)
		{
			$intCharCount += Utf8::strlen(static::decodeEntities($strChunk));

			if ($intCharCount++ <= $intNumberOfChars)
			{
				$arrWords[] = $strChunk;
				continue;
			}

			// If the first word is longer than $intNumberOfChars already, shorten it
			// with Utf8::substr() so the method does not return an empty string.
			if (empty($arrWords))
			{
				$arrWords[] = Utf8::substr($strChunk, 0, $intNumberOfChars);
			}

			if ($strEllipsis !== false)
			{
				$blnAddEllipsis = true;
			}

			break;
		}

		// Deprecated since Contao 4.0, to be removed in Contao 5.0
		if ($strEllipsis === true)
		{
			@trigger_error('Passing "true" as third argument to StringUtil::substr() has been deprecated and will no longer work in Contao 5.0. Pass the ellipsis string instead.', E_USER_DEPRECATED);

			$strEllipsis = ' …';
		}

		return implode(' ', $arrWords) . ($blnAddEllipsis ? $strEllipsis : '');
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
	public static function substrHtml($strString, $intNumberOfChars)
	{
		$strReturn = '';
		$intCharCount = 0;
		$arrOpenTags = array();
		$arrTagBuffer = array();
		$arrEmptyTags = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr');

		$strString = preg_replace('/[\t\n\r]+/', ' ', $strString);
		$strString = strip_tags($strString, \Config::get('allowedTags'));
		$strString = preg_replace('/ +/', ' ', $strString);

		// Seperate tags and text
		$arrChunks = preg_split('/(<[^>]+>)/', $strString, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		for ($i=0, $c=\count($arrChunks); $i<$c; $i++)
		{
			// Buffer tags to include them later
			if (preg_match('/<([^>]+)>/', $arrChunks[$i]))
			{
				$arrTagBuffer[] = $arrChunks[$i];
				continue;
			}

			$buffer = $arrChunks[$i];

			// Get the substring of the current text
			if (!$arrChunks[$i] = static::substr($arrChunks[$i], ($intNumberOfChars - $intCharCount), false))
			{
				break;
			}

			$blnModified = ($buffer !== $arrChunks[$i]);
			$intCharCount += Utf8::strlen(static::decodeEntities($arrChunks[$i]));

			if ($intCharCount <= $intNumberOfChars)
			{
				foreach ($arrTagBuffer as $strTag)
				{
					$strTagName = strtolower(trim($strTag));

					// Extract the tag name (see #5669)
					if (($pos = strpos($strTagName, ' ')) !== false)
					{
						$strTagName = substr($strTagName, 1, $pos - 1);
					}
					else
					{
						$strTagName = substr($strTagName, 1, -1);
					}

					// Skip empty tags
					if (\in_array($strTagName, $arrEmptyTags))
					{
						continue;
					}

					// Store opening tags in the open_tags array
					if (strncmp($strTagName, '/', 1) !== 0)
					{
						if (!empty($arrChunks[$i]) || $i<$c)
						{
							$arrOpenTags[] = $strTagName;
						}

						continue;
					}

					// Closing tags will be removed from the "open tags" array
					if (!empty($arrChunks[$i]) || $i<$c)
					{
						$arrOpenTags = array_values($arrOpenTags);

						for ($j=\count($arrOpenTags)-1; $j>=0; $j--)
						{
							if ($strTagName == '/' . $arrOpenTags[$j])
							{
								unset($arrOpenTags[$j]);
								break;
							}
						}
					}
				}

				// If the current chunk contains text, add tags and text to the return string
				if (\strlen($arrChunks[$i]) || $i<$c)
				{
					$strReturn .= implode('', $arrTagBuffer) . $arrChunks[$i];
				}

				// Stop after the first shortened chunk (see #7311)
				if ($blnModified)
				{
					break;
				}

				$arrTagBuffer = array();
				continue;
			}

			break;
		}

		// Close all remaining open tags
		krsort($arrOpenTags);

		foreach ($arrOpenTags as $strTag)
		{
			$strReturn .= '</' . $strTag . '>';
		}

		return trim($strReturn);
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
	public static function decodeEntities($strString, $strQuoteStyle=ENT_COMPAT, $strCharset=null)
	{
		if ($strString == '')
		{
			return '';
		}

		if ($strCharset === null)
		{
			$strCharset = \Config::get('characterSet');
		}

		$strString = preg_replace('/(&#*\w+)[\x00-\x20]+;/i', '$1;', $strString);
		$strString = preg_replace('/(&#x*)([0-9a-f]+);/i', '$1$2;', $strString);

		return html_entity_decode($strString, $strQuoteStyle, $strCharset);
	}

	/**
	 * Restore basic entities
	 *
	 * @param string $strBuffer The string with the tags to be replaced
	 *
	 * @return string The string with the original entities
	 */
	public static function restoreBasicEntities($strBuffer)
	{
		return str_replace(array('[&]', '[&amp;]', '[lt]', '[gt]', '[nbsp]', '[-]'), array('&amp;', '&amp;', '&lt;', '&gt;', '&nbsp;', '&shy;'), $strBuffer);
	}

	/**
	 * Generate an alias from a string
	 *
	 * @param string $strString The string
	 *
	 * @return string The alias
	 */
	public static function generateAlias($strString)
	{
		$strString = static::decodeEntities($strString);
		$strString = static::restoreBasicEntities($strString);
		$strString = static::standardize(strip_tags($strString));

		// Remove the prefix if the alias is not numeric (see #707)
		if (strncmp($strString, 'id-', 3) === 0 && !is_numeric($strSubstr = substr($strString, 3)))
		{
			$strString = $strSubstr;
		}

		return $strString;
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
	public static function censor($strString, $varWords, $strReplace='')
	{
		foreach ((array) $varWords as $strWord)
		{
			$strString = preg_replace('/\b(' . str_replace('\*', '\w*?', preg_quote($strWord, '/')) . ')\b/i', $strReplace, $strString);
		}

		return $strString;
	}

	/**
	 * Encode all e-mail addresses within a string
	 *
	 * @param string $strString The string to encode
	 *
	 * @return string The encoded string
	 */
	public static function encodeEmail($strString)
	{
		if (strpos($strString, '@') === false)
		{
			return $strString;
		}

		$arrEmails = static::extractEmail($strString, \Config::get('allowedTags'));

		foreach ($arrEmails as $strEmail)
		{
			$strEncoded = '';
			$arrCharacters = Utf8::str_split($strEmail);

			foreach ($arrCharacters as $index => $strCharacter)
			{
				$strEncoded .= sprintf(($index % 2) ? '&#x%X;' : '&#%s;', Utf8::ord($strCharacter));
			}

			$strString = str_replace($strEmail, $strEncoded, $strString);
		}

		return str_replace('mailto:', '&#109;&#97;&#105;&#108;&#116;&#111;&#58;', $strString);
	}

	/**
	 * Extract all e-mail addresses from a string
	 *
	 * @param string $strString      The string
	 * @param string $strAllowedTags A list of allowed HTML tags
	 *
	 * @return array The e-mail addresses
	 */
	public static function extractEmail($strString, $strAllowedTags='')
	{
		$arrEmails = array();

		if (strpos($strString, '@') === false)
		{
			return $arrEmails;
		}

		// Find all mailto: addresses
		preg_match_all('/mailto:(?:[^\x00-\x20\x22\x40\x7F]{1,64}+|\x22[^\x00-\x1F\x7F]{1,64}?\x22)@(?:\[(?:IPv)?[a-f0-9.:]{1,47}]|[\w.-]{1,252}\.[a-z]{2,63}\b)/u', $strString, $matches);

		foreach ($matches[0] as &$strEmail)
		{
			$strEmail = str_replace('mailto:', '', $strEmail);

			if (\Validator::isEmail($strEmail))
			{
				$arrEmails[] = $strEmail;
			}
		}

		unset($strEmail);

		// Encode opening arrow brackets (see #3998)
		$strString = preg_replace_callback('@</?([^\s<>/]*)@', function ($matches) use ($strAllowedTags)
		{
			if ($matches[1] == '' || stripos($strAllowedTags, '<' . strtolower($matches[1]) . '>') === false)
			{
				$matches[0] = str_replace('<', '&lt;', $matches[0]);
			}

			return $matches[0];
		}, $strString);

		// Find all addresses in the plain text
		preg_match_all('/(?:[^\x00-\x20\x22\x40\x7F]{1,64}|\x22[^\x00-\x1F\x7F]{1,64}?\x22)@(?:\[(?:IPv)?[a-f0-9.:]{1,47}]|[\w.-]{1,252}\.[a-z]{2,63}\b)/u', strip_tags($strString), $matches);

		foreach ($matches[0] as &$strEmail)
		{
			$strEmail = str_replace('&lt;', '<', $strEmail);

			if (\Validator::isEmail($strEmail))
			{
				$arrEmails[] = $strEmail;
			}
		}

		return array_unique($arrEmails);
	}

	/**
	 * Split a friendly-name e-mail address and return name and e-mail as array
	 *
	 * @param string $strEmail A friendly-name e-mail address
	 *
	 * @return array An array with name and e-mail address
	 */
	public static function splitFriendlyEmail($strEmail)
	{
		if (strpos($strEmail, '<') !== false)
		{
			return array_map('trim', explode(' <', str_replace('>', '', $strEmail)));
		}

		if (strpos($strEmail, '[') !== false)
		{
			return array_map('trim', explode(' [', str_replace(']', '', $strEmail)));
		}

		return array('', $strEmail);
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
	public static function wordWrap($strString, $strLength=75, $strBreak="\n")
	{
		return wordwrap($strString, $strLength, $strBreak);
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
	public static function highlight($strString, $strPhrase, $strOpeningTag='<strong>', $strClosingTag='</strong>')
	{
		if ($strString == '' || $strPhrase == '')
		{
			return $strString;
		}

		return preg_replace('/(' . preg_quote($strPhrase, '/') . ')/i', $strOpeningTag . '\\1' . $strClosingTag, $strString);
	}

	/**
	 * Split a string of comma separated values
	 *
	 * @param string $strString    The string to split
	 * @param string $strDelimiter An optional delimiter
	 *
	 * @return array The string chunks
	 */
	public static function splitCsv($strString, $strDelimiter=',')
	{
		$arrValues = preg_split('/' . $strDelimiter . '(?=(?:[^"]*"[^"]*")*(?![^"]*"))/', $strString);

		foreach ($arrValues as $k=>$v)
		{
			$arrValues[$k] = trim($v, ' "');
		}

		return $arrValues;
	}

	/**
	 * Convert a string to XHTML
	 *
	 * @param string $strString The HTML5 string
	 *
	 * @return string The XHTML string
	 */
	public static function toXhtml($strString)
	{
		$arrPregReplace = array
		(
			'/<(br|hr|img)([^>]*)>/i' => '<$1$2 />', // Close stand-alone tags
			'/ border="[^"]*"/'       => ''          // Remove deprecated attributes
		);

		$arrStrReplace = array
		(
			'/ />'             => ' />',        // Fix incorrectly closed tags
			'<b>'              => '<strong>',   // Replace <b> with <strong>
			'</b>'             => '</strong>',
			'<i>'              => '<em>',       // Replace <i> with <em>
			'</i>'             => '</em>',
			'<u>'              => '<span style="text-decoration:underline">',
			'</u>'             => '</span>',
			' target="_self"'  => '',
			' target="_blank"' => ' onclick="return !window.open(this.href)"'
		);

		$strString = preg_replace(array_keys($arrPregReplace), array_values($arrPregReplace), $strString);
		$strString = str_ireplace(array_keys($arrStrReplace), array_values($arrStrReplace), $strString);

		return $strString;
	}

	/**
	 * Convert a string to HTML5
	 *
	 * @param string $strString The XHTML string
	 *
	 * @return string The HTML5 string
	 */
	public static function toHtml5($strString)
	{
		$arrPregReplace = array
		(
			'/<(br|hr|img)([^>]*) \/>/i'                  => '<$1$2>',             // Close stand-alone tags
			'/ (cellpadding|cellspacing|border)="[^"]*"/' => '',                   // Remove deprecated attributes
			'/ rel="lightbox(\[([^\]]+)\])?"/'            => ' data-lightbox="$2"' // see #4073
		);

		$arrStrReplace = array
		(
			'<u>'                                              => '<span style="text-decoration:underline">',
			'</u>'                                             => '</span>',
			' target="_self"'                                  => '',
			' onclick="window.open(this.href); return false"'  => ' target="_blank"',
			' onclick="window.open(this.href);return false"'   => ' target="_blank"',
			' onclick="window.open(this.href); return false;"' => ' target="_blank"'
		);

		$strString = preg_replace(array_keys($arrPregReplace), array_values($arrPregReplace), $strString);
		$strString = str_ireplace(array_keys($arrStrReplace), array_values($arrStrReplace), $strString);

		return $strString;
	}

	/**
	 * Parse simple tokens
	 *
	 * @param string $strString The string to be parsed
	 * @param array  $arrData   The replacement data
	 *
	 * @return string The converted string
	 *
	 * @throws \Exception                If $strString cannot be parsed
	 * @throws \InvalidArgumentException If there are incorrectly formatted if-tags
	 */
	public static function parseSimpleTokens($strString, $arrData)
	{
		$strReturn = '';

		$replaceTokens = function ($strSubject) use ($arrData)
		{
			// Replace tokens
			return preg_replace_callback
			(
				'/##([^=!<>\s]+?)##/',
				function (array $matches) use ($arrData)
				{
					if (!\array_key_exists($matches[1], $arrData))
					{
						\System::getContainer()
							->get('monolog.logger.contao')
							->log(LogLevel::INFO, sprintf('Tried to parse unknown simple token "%s".', $matches[1]))
						;

						return '##' . $matches[1] . '##';
					}

					return $arrData[$matches[1]];
				},
				$strSubject
			);
		};

		$evaluateExpression = function ($strExpression) use ($arrData)
		{
			if (!preg_match('/^([^=!<>\s]+)([=!<>]+)(.+)$/s', $strExpression, $arrMatches))
			{
				return false;
			}

			$strToken = $arrMatches[1];
			$strOperator = $arrMatches[2];
			$strValue = $arrMatches[3];

			if (!\array_key_exists($strToken, $arrData))
			{
				\System::getContainer()
					->get('monolog.logger.contao')
					->log(LogLevel::INFO, sprintf('Tried to evaluate unknown simple token "%s".', $strToken))
				;

				return false;
			}

			$varTokenValue = $arrData[$strToken];

			if (is_numeric($strValue))
			{
				if (strpos($strValue, '.') === false)
				{
					$varValue = (int) $strValue;
				}
				else
				{
					$varValue = (float) $strValue;
				}
			}
			elseif (strtolower($strValue) === 'true')
			{
				$varValue = true;
			}
			elseif (strtolower($strValue) === 'false')
			{
				$varValue = false;
			}
			elseif (strtolower($strValue) === 'null')
			{
				$varValue = null;
			}
			elseif (substr($strValue, 0, 1) === '"' && substr($strValue, -1) === '"')
			{
				$varValue = str_replace('\"', '"', substr($strValue, 1, -1));
			}
			elseif (substr($strValue, 0, 1) === "'" && substr($strValue, -1) === "'")
			{
				$varValue = str_replace("\\'", "'", substr($strValue, 1, -1));
			}
			else
			{
				throw new \InvalidArgumentException(sprintf('Unknown data type of comparison value "%s".', $strValue));
			}

			switch ($strOperator)
			{
				case '==':
					return $varTokenValue == $varValue;

				case '!=':
					return $varTokenValue != $varValue;

				case '===':
					return $varTokenValue === $varValue;

				case '!==':
					return $varTokenValue !== $varValue;

				case '<':
					return $varTokenValue < $varValue;

				case '>':
					return $varTokenValue > $varValue;

				case '<=':
					return $varTokenValue <= $varValue;

				case '>=':
					return $varTokenValue >= $varValue;

				default:
					throw new \InvalidArgumentException(sprintf('Unknown simple token comparison operator "%s".', $strOperator));
			}
		};

		// The last item is true if it is inside a matching if-tag
		$arrStack = array(true);

		// The last item is true if any if/elseif at that level was true
		$arrIfStack = array(true);

		// Tokenize the string into tag and text blocks
		$arrTags = preg_split('/({[^{}]+})\n?/', $strString, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		// Parse the tokens
		foreach ($arrTags as $strTag)
		{
			// True if it is inside a matching if-tag
			$blnCurrent = $arrStack[\count($arrStack) - 1];
			$blnCurrentIf = $arrIfStack[\count($arrIfStack) - 1];

			if (strncmp($strTag, '{if ', 4) === 0)
			{
				$blnExpression = $evaluateExpression(substr($strTag, 4, -1));
				$arrStack[] = $blnCurrent && $blnExpression;
				$arrIfStack[] = $blnExpression;
			}
			elseif (strncmp($strTag, '{elseif ', 8) === 0)
			{
				$blnExpression = $evaluateExpression(substr($strTag, 8, -1));
				array_pop($arrStack);
				array_pop($arrIfStack);
				$arrStack[] = !$blnCurrentIf && $arrStack[\count($arrStack) - 1] && $blnExpression;
				$arrIfStack[] = $blnCurrentIf || $blnExpression;
			}
			elseif (strncmp($strTag, '{else}', 6) === 0)
			{
				array_pop($arrStack);
				array_pop($arrIfStack);
				$arrStack[] = !$blnCurrentIf && $arrStack[\count($arrStack) - 1];
				$arrIfStack[] = true;
			}
			elseif (strncmp($strTag, '{endif}', 7) === 0)
			{
				array_pop($arrStack);
				array_pop($arrIfStack);
			}
			elseif ($blnCurrent)
			{
				$strReturn .= $replaceTokens($strTag);
			}
		}

		// Throw an exception if there is an error
		if (\count($arrStack) !== 1)
		{
			throw new \Exception('Error parsing simple tokens');
		}

		return $strReturn;
	}

	/**
	 * Convert a UUID string to binary data
	 *
	 * @param string $uuid The UUID string
	 *
	 * @return string The binary data
	 */
	public static function uuidToBin($uuid)
	{
		return hex2bin(str_replace('-', '', $uuid));
	}

	/**
	 * Get a UUID string from binary data
	 *
	 * @param string $data The binary data
	 *
	 * @return string The UUID string
	 */
	public static function binToUuid($data)
	{
		return implode('-', unpack('H8time_low/H4time_mid/H4time_high/H4clock_seq/H12node', $data));
	}

	/**
	 * Convert file paths inside "src" attributes to insert tags
	 *
	 * @param string $data The markup string
	 *
	 * @return string The markup with file paths converted to insert tags
	 */
	public static function srcToInsertTag($data)
	{
		$return = '';
		$paths = preg_split('/((src|href)="([^"]+)")/i', $data, -1, PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0, $c=\count($paths); $i<$c; $i+=4)
		{
			$return .= $paths[$i];

			if (!isset($paths[$i+1]))
			{
				continue;
			}

			$file = \FilesModel::findByPath($paths[$i+3]);

			if ($file !== null)
			{
				$return .= $paths[$i+2] . '="{{file::' . static::binToUuid($file->uuid) . '}}"';
			}
			else
			{
				$return .= $paths[$i+2] . '="' . $paths[$i+3] . '"';
			}
		}

		return $return;
	}

	/**
	 * Convert insert tags inside "src" attributes to file paths
	 *
	 * @param string $data The markup string
	 *
	 * @return string The markup with insert tags converted to file paths
	 */
	public static function insertTagToSrc($data)
	{
		$return = '';
		$paths = preg_split('/((src|href)="([^"]*){{file::([^"}]+)}}")/i', $data, -1, PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0, $c=\count($paths); $i<$c; $i+=5)
		{
			$return .= $paths[$i];

			if (!isset($paths[$i+1]))
			{
				continue;
			}

			$file = \FilesModel::findByUuid($paths[$i+4]);

			if ($file !== null)
			{
				$return .= $paths[$i+2] . '="' . $paths[$i+3] . $file->path . '"';
			}
			else
			{
				$return .= $paths[$i+2] . '="' . $paths[$i+3] . $paths[$i+4] . '"';
			}
		}

		return $return;
	}

	/**
	 * Sanitize a file name
	 *
	 * @param string $strName The file name
	 *
	 * @return string The sanitized file name
	 */
	public static function sanitizeFileName($strName)
	{
		// Remove invisible control characters and unused code points
		$strName = preg_replace('/[\pC]/u', '', $strName);

		if ($strName === null)
		{
			throw new \InvalidArgumentException('The file name could not be sanitzied');
		}

		// Remove special characters not supported on e.g. Windows
		$strName = str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|'), '-', $strName);

		return $strName;
	}

	/**
	 * Resolve a flagged URL such as assets/js/core.js|static|10184084
	 *
	 * @param string $url The URL
	 *
	 * @return \stdClass The options object
	 */
	public static function resolveFlaggedUrl(&$url)
	{
		$options = new \stdClass();

		// Defaults
		$options->static = false;
		$options->media  = null;
		$options->mtime  = null;
		$options->async  = false;

		$chunks = explode('|', $url);

		// Remove the flags from the URL
		$url = $chunks[0];

		for ($i=1, $c=\count($chunks); $i<$c; $i++)
		{
			if (empty($chunks[$i]))
			{
				continue;
			}

			switch ($chunks[$i])
			{
				case 'static':
					$options->static = true;
					break;

				case 'async':
					$options->async = true;
					break;

				case is_numeric($chunks[$i]):
					$options->mtime = $chunks[$i];
					break;

				default:
					$options->media = $chunks[$i];
					break;
			}
		}

		return $options;
	}

	/**
	 * Convert the character encoding
	 *
	 * @param string $str  The input string
	 * @param string $to   The target character set
	 * @param string $from An optional source character set
	 *
	 * @return string The converted string
	 */
	public static function convertEncoding($str, $to, $from=null)
	{
		if ($str == '')
		{
			return '';
		}

		if (!$from)
		{
			$from = mb_detect_encoding($str, 'ASCII,ISO-2022-JP,UTF-8,EUC-JP,ISO-8859-1');
		}

		if ($from == $to)
		{
			return $str;
		}

		if ($from == 'UTF-8' && $to == 'ISO-8859-1')
		{
			return utf8_decode($str);
		}

		if ($from == 'ISO-8859-1' && $to == 'UTF-8')
		{
			return utf8_encode($str);
		}

		return mb_convert_encoding($str, $to, $from);
	}

	/**
	 * Convert special characters to HTML entities preventing double conversions
	 *
	 * @param string  $strString          The input string
	 * @param boolean $blnStripInsertTags True to strip insert tags
	 * @param boolean $blnDoubleEncode    True to encode existing html entities
	 *
	 * @return string The converted string
	 */
	public static function specialchars($strString, $blnStripInsertTags=false, $blnDoubleEncode=false)
	{
		if ($blnStripInsertTags)
		{
			$strString = static::stripInsertTags($strString);
		}

		// Use ENT_COMPAT here (see #4889)
		return htmlspecialchars($strString, ENT_COMPAT, \Config::get('characterSet'), $blnDoubleEncode);
	}

	/**
	 * Remove Contao insert tags from a string
	 *
	 * @param string $strString The input string
	 *
	 * @return string The converted string
	 */
	public static function stripInsertTags($strString)
	{
		$count = 0;

		do
		{
			$strString = preg_replace('/{{[^{}]*}}/', '', $strString, -1, $count);
		} while ($count > 0);

		return $strString;
	}

	/**
	 * Standardize a parameter (strip special characters and convert spaces)
	 *
	 * @param string  $strString            The input string
	 * @param boolean $blnPreserveUppercase True to preserver uppercase characters
	 *
	 * @return string The converted string
	 */
	public static function standardize($strString, $blnPreserveUppercase=false)
	{
		$arrSearch = array('/[^\pN\pL \.\&\/_-]+/u', '/[ \.\&\/-]+/');
		$arrReplace = array('', '-');

		$strString = html_entity_decode($strString, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
		$strString = static::stripInsertTags($strString);
		$strString = preg_replace($arrSearch, $arrReplace, $strString);

		if (is_numeric(substr($strString, 0, 1)))
		{
			$strString = 'id-' . $strString;
		}

		if (!$blnPreserveUppercase)
		{
			$strString = Utf8::strtolower($strString);
		}

		return trim($strString, '-');
	}

	/**
	 * Return an unserialized array or the argument
	 *
	 * @param mixed   $varValue      The serialized string
	 * @param boolean $blnForceArray True to always return an array
	 *
	 * @return array|string|null The array, an empty string or null
	 */
	public static function deserialize($varValue, $blnForceArray=false)
	{
		// Already an array
		if (\is_array($varValue))
		{
			return $varValue;
		}

		// Null
		if ($varValue === null)
		{
			return $blnForceArray ? array() : null;
		}

		// Not a string
		if (!\is_string($varValue))
		{
			return $blnForceArray ? array($varValue) : $varValue;
		}

		// Empty string
		if (trim($varValue) == '')
		{
			return $blnForceArray ? array() : '';
		}

		// Potentially including an object (see #6724)
		if (preg_match('/[OoC]:\+?[0-9]+:"/', $varValue))
		{
			trigger_error('StringUtil::deserialize() does not allow serialized objects', E_USER_WARNING);

			return $blnForceArray ? array($varValue) : $varValue;
		}

		$varUnserialized = @unserialize($varValue);

		if (\is_array($varUnserialized))
		{
			$varValue = $varUnserialized;
		}
		elseif ($blnForceArray)
		{
			$varValue = array($varValue);
		}

		return $varValue;
	}

	/**
	 * Split a string into fragments, remove whitespace and return fragments as array
	 *
	 * @param string $strPattern The split pattern
	 * @param string $strString  The input string
	 *
	 * @return array The fragments array
	 */
	public static function trimsplit($strPattern, $strString)
	{
		// Split
		if (\strlen($strPattern) == 1)
		{
			$arrFragments = array_map('trim', explode($strPattern, $strString));
		}
		else
		{
			$arrFragments = array_map('trim', preg_split('/' . $strPattern . '/ui', $strString));
		}

		// Empty array
		if (\count($arrFragments) < 2 && !\strlen($arrFragments[0]))
		{
			$arrFragments = array();
		}

		return $arrFragments;
	}

	/**
	 * Strip the Contao root dir from the given absolute path
	 *
	 * @param string $path
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function stripRootDir($path)
	{
		static $length = null;

		if ($length === null)
		{
			$length = \strlen(TL_ROOT);
		}

		if (strncmp($path, TL_ROOT, $length) !== 0 || \strlen($path) <= $length || ($path[$length] !== '/' && $path[$length] !== '\\'))
		{
			throw new \InvalidArgumentException(sprintf('Path "%s" is not inside the Contao root dir', $path));
		}

		return (string) substr($path, $length + 1);
	}
}
