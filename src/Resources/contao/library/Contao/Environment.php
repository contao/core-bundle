<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Symfony\Component\HttpFoundation\Request;

/**
 * Reads the environment variables
 *
 * The class returns the environment variables (which are stored in the PHP
 * $_SERVER array) independent of the operating system.
 *
 * Usage:
 *
 *     echo Environment::get('scriptName');
 *     echo Environment::get('requestUri');
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated since Contao 4.3, to be removed in Contao 5.0.
 *             You should not use this class anymore but use the request or request stack directly.
 */
class Environment
{
	/**
	 * @var Request
	 */
	private static $objRequest;

	/**
	 * Object instance (Singleton)
	 * @var Environment
	 */
	protected static $objInstance;

	/**
	 * The SAPI name
	 * @var string
	 */
	protected static $strSapi = PHP_SAPI;

	/**
	 * Cache
	 * @var array
	 */
	protected static $arrCache = array();

	/**
	 * Gateway function used by Contao3RequestSynchronizingListener to push the current (sub-)request.
	 *
	 * @param Request $objRequest The request.
	 */
	public static function setRequest(Request $objRequest = null)
	{
		self::$objRequest = $objRequest;
		self::reset();
	}

	/**
	 * Return an environment variable
	 *
	 * @param string $strKey The variable name
	 *
	 * @return mixed The variable value
	 */
	public static function get($strKey)
	{
		if (isset(static::$arrCache[$strKey]))
		{
			return static::$arrCache[$strKey];
		}

		if (in_array($strKey, get_class_methods(__CLASS__)))
		{
			static::$arrCache[$strKey] = static::$strKey();
		}
		else
		{
			$arrChunks = preg_split('/([A-Z][a-z]*)/', $strKey, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
			$strServerKey = strtoupper(implode('_', $arrChunks));
			static::$arrCache[$strKey] = self::$objRequest->server->get($strServerKey);
		}

		return static::$arrCache[$strKey];
	}


	/**
	 * Set an environment variable
	 *
	 * @param string $strKey   The variable name
	 * @param mixed  $varValue The variable value
	 */
	public static function set($strKey, $varValue)
	{
		static::$arrCache[$strKey] = $varValue;
	}


	/**
	 * Reset the internal cache
	 */
	public static function reset()
	{
		static::$arrCache = array();
	}


	/**
	 * Return the absolute path to the script (e.g. /home/www/html/website/index.php)
	 *
	 * @return string The absolute path to the script
	 */
	protected static function scriptFilename()
	{
		if ((static::$strSapi == 'cgi' || static::$strSapi == 'isapi' || static::$strSapi == 'cgi-fcgi' || static::$strSapi == 'fpm-fcgi'))
		{
			$script = self::$objRequest->server->get('ORIG_PATH_TRANSLATED', self::$objRequest->server->get('PATH_TRANSLATED'));
		}

		if (empty($script))
		{
			$script = self::$objRequest->server->get('ORIG_SCRIPT_FILENAME', self::$objRequest->server->get('SCRIPT_FILENAME'));
		}

		return str_replace('//', '/', strtr($script, '\\', '/'));
	}


	/**
	 * Return the relative path to the script (e.g. /website/index.php)
	 *
	 * @return string The relative path to the script
	 */
	protected static function scriptName()
	{
		return self::$objRequest->getScriptName();
	}


	/**
	 * Alias for scriptName()
	 *
	 * @return string The script name
	 */
	protected static function phpSelf()
	{
		return static::scriptName();
	}


	/**
	 * Return the document root (e.g. /home/www/user/)
	 *
	 * Calculated as SCRIPT_FILENAME minus SCRIPT_NAME as some CGI versions
	 * and mod-rewrite rules might return an incorrect DOCUMENT_ROOT.
	 *
	 * @return string The document root
	 */
	protected static function documentRoot()
	{
		$strDocumentRoot = '';
		$arrUriSegments = array();
		$scriptName = static::get('scriptName');
		$scriptFilename = static::get('scriptFilename');

		// Fallback to DOCUMENT_ROOT if SCRIPT_FILENAME and SCRIPT_NAME point to different files
		if (basename($scriptName) != basename($scriptFilename))
		{
			return str_replace('//', '/', strtr(realpath(self::$objRequest->server->get('DOCUMENT_ROOT')), '\\', '/'));
		}

		if (substr($scriptFilename, 0, 1) == '/')
		{
			$strDocumentRoot = '/';
		}

		$arrSnSegments = explode('/', strrev($scriptName));
		$arrSfnSegments = explode('/', strrev($scriptFilename));

		foreach ($arrSfnSegments as $k=>$v)
		{
			if (@$arrSnSegments[$k] != $v)
			{
				$arrUriSegments[] = $v;
			}
		}

		$strDocumentRoot .= strrev(implode('/', $arrUriSegments));

		if (strlen($strDocumentRoot) < 2)
		{
			$strDocumentRoot = substr($scriptFilename, 0, -(strlen($strDocumentRoot) + 1));
		}

		return str_replace('//', '/', strtr(realpath($strDocumentRoot), '\\', '/'));
	}


	/**
	 * Return the query string (e.g. id=2)
	 *
	 * @return string The query string
	 */
	protected static function queryString()
	{
		return static::encodeRequestString(self::$objRequest->getQueryString());
	}


	/**
	 * Return the request URI [path]?[query] (e.g. /contao/index.php?id=2)
	 *
	 * @return string The request URI
	 */
	protected static function requestUri()
	{
		return static::encodeRequestString(self::$objRequest->getRequestUri());
	}


	/**
	 * Return the first eight accepted languages as array
	 *
	 * @return array The languages array
	 */
	protected static function httpAcceptLanguage()
	{
		return array_slice(array_map(function($strLanguage) { return strtr($strLanguage, '_', '-'); }, self::$objRequest->getLanguages()), 0, 8);
	}


	/**
	 * Return accepted encoding types as array
	 *
	 * @return array The encoding types array
	 */
	protected static function httpAcceptEncoding()
	{
		return self::$objRequest->getEncodings();
	}


	/**
	 * Return the user agent as string
	 *
	 * @return string The user agent string
	 */
	protected static function httpUserAgent()
	{
		$ua = strip_tags(self::$objRequest->headers->get('User-Agent'));
		$ua = preg_replace('/javascript|vbscri?pt|script|applet|alert|document|write|cookie/i', '', $ua);

		return substr($ua, 0, 255);
	}


	/**
	 * Return the HTTP Host
	 *
	 * @return string The host name
	 */
	protected static function httpHost()
	{
		return preg_replace('/[^A-Za-z0-9[\].:-]/', '', self::$objRequest->getHttpHost());
	}


	/**
	 * Return the HTTP X-Forwarded-Host
	 *
	 * @return string The name of the X-Forwarded-Host
	 */
	protected static function httpXForwardedHost()
	{
		// FIXME: is broken.
		return preg_replace('/[^A-Za-z0-9[\].:-]/', '', self::$objRequest->headers->get('X-Forwarded-For'));
	}


	/**
	 * Return true if the current page was requested via an SSL connection
	 *
	 * @return boolean True if SSL is enabled
	 */
	protected static function ssl()
	{
		return self::$objRequest->server->get('SSL_SESSION_ID') || (self::$objRequest->server->get('HTTPS') == 'on') || (self::$objRequest->server->get('HTTPS') == 1);
	}


	/**
	 * Return the current URL without path or query string
	 *
	 * @return string The URL
	 */
	protected static function url()
	{
		$host = static::get('httpHost');
		$xhost = static::get('httpXForwardedHost');

		// SSL proxy
		if ($xhost != '' && $xhost == \Config::get('sslProxyDomain'))
		{
			return 'https://' .  $xhost . '/' . $host;
		}

		return (static::get('ssl') ? 'https://' : 'http://') . $host;
	}


	/**
	 * Return the current URL with path or query string
	 *
	 * @return string The URL
	 */
	protected static function uri()
	{
		return static::get('url') . static::get('requestUri');
	}


	/**
	 * Return the real REMOTE_ADDR even if a proxy server is used
	 *
	 * @return string The IP address of the client
	 */
	protected static function ip()
	{
		return self::$objRequest->getClientIp();
	}


	/**
	 * Return the SERVER_ADDR
	 *
	 * @return string The IP address of the server
	 */
	protected static function server()
	{
		$strServer = self::$objRequest->server->get('SERVER_ADDR', self::$objRequest->server->get('LOCAL_ADDR'));

		// Special workaround for Strato users
		if (empty($strServer))
		{
			$strServer = @gethostbyname(self::$objRequest->server->get('SERVER_NAME'));
		}

		return $strServer;
	}


	/**
	 * Return the relative path to the base directory (e.g. /path)
	 *
	 * @return string The relative path to the installation
	 */
	protected static function path()
	{
		return self::$objRequest->getBasePath();
	}


	/**
	 * Return the relativ path to the script (e.g. index.php)
	 *
	 * @return string The relative path to the script
	 */
	protected static function script()
	{
		return preg_replace('/^' . preg_quote(static::get('path'), '/') . '\/?/', '', static::get('scriptName'));
	}


	/**
	 * Return the relativ path to the script and include the request (e.g. index.php?id=2)
	 *
	 * @return string The relative path to the script including the request string
	 */
	protected static function request()
	{
		return preg_replace('/^' . preg_quote(static::get('path'), '/') . '\/?/', '', static::get('requestUri'));
	}


	/**
	 * Return the request string without the script name (e.g. en/news.html)
	 *
	 * @return string The base URL
	 */
	protected static function relativeRequest()
	{
		return preg_replace('/^' . preg_quote(static::get('script'),  '/') . '\/?/', '', static::get('request'));
	}


	/**
	 * Return the request string without the index.php fragment
	 *
	 * @return string The request string without the index.php fragment
	 */
	protected static function indexFreeRequest()
	{
		$strRequest = static::get('request');

		if ($strRequest == static::get('script'))
		{
			return '';
		}

		return $strRequest;
	}


	/**
	 * Return the URL and path that can be used in a <base> tag
	 *
	 * @return string The base URL
	 */
	protected static function base()
	{
		return static::get('url') . static::get('path') . '/';
	}


	/**
	 * Return the host name
	 *
	 * @return string The host name
	 */
	protected static function host()
	{
		return static::get('httpHost');
	}


	/**
	 * Return true on Ajax requests
	 *
	 * @return boolean True if it is an Ajax request
	 */
	protected static function isAjaxRequest()
	{
		return self::$objRequest->headers->get('X-Requested-With') == 'XMLHttpRequest';
	}


	/**
	 * Return the operating system and the browser name and version
	 *
	 * @return object The agent information
	 */
	protected static function agent()
	{
		$ua = static::get('httpUserAgent');

		$return = new \stdClass();
		$return->string = $ua;

		$os = 'unknown';
		$mobile = false;
		$browser = 'other';
		$shorty = '';
		$version = '';
		$engine = '';

		// Operating system
		foreach (\Config::get('os') as $k=>$v)
		{
			if (stripos($ua, $k) !== false)
			{
				$os = $v['os'];
				$mobile = $v['mobile'];
				break;
			}
		}

		$return->os = $os;

		// Browser and version
		foreach (\Config::get('browser') as $k=>$v)
		{
			if (stripos($ua, $k) !== false)
			{
				$browser = $v['browser'];
				$shorty  = $v['shorty'];
				$version = preg_replace($v['version'], '$1', $ua);
				$engine  = $v['engine'];
				break;
			}
		}

		$versions = explode('.', $version);
		$version  = $versions[0];

		$return->class = $os . ' ' . $browser . ' ' . $engine;

		// Add the version number if available
		if ($version != '')
		{
			$return->class .= ' ' . $shorty . $version;
		}

		// Android tablets are not mobile (see #4150 and #5869)
		if ($os == 'android' && $engine != 'presto' && stripos($ua, 'mobile') === false)
		{
			$mobile = false;
		}

		// Mark mobile devices
		if ($mobile)
		{
			$return->class .= ' mobile';
		}

		$return->browser  = $browser;
		$return->shorty   = $shorty;
		$return->version  = $version;
		$return->engine   = $engine;
		$return->versions = $versions;
		$return->mobile   = $mobile;

		return $return;
	}


	/**
	 * Encode a request string preserving certain reserved characters
	 *
	 * @param string $strRequest The request string
	 *
	 * @return string The encoded request string
	 */
	protected static function encodeRequestString($strRequest)
	{
		return preg_replace_callback('/[^A-Za-z0-9\-_.~&=+,\/?%\[\]]+/', function($matches) { return rawurlencode($matches[0]); }, $strRequest);
	}


	/**
	 * Prevent direct instantiation (Singleton)
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The Environment class is now static.
	 */
	protected function __construct() {}


	/**
	 * Prevent cloning of the object (Singleton)
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The Environment class is now static.
	 */
	final public function __clone() {}


	/**
	 * Return an environment variable
	 *
	 * @param string $strKey The variable name
	 *
	 * @return string The variable value
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             Use Environment::get() instead.
	 */
	public function __get($strKey)
	{
		return static::get($strKey);
	}


	/**
	 * Set an environment variable
	 *
	 * @param string $strKey   The variable name
	 * @param mixed  $varValue The variable value
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             Use Environment::set() instead.
	 */
	public function __set($strKey, $varValue)
	{
		static::set($strKey, $varValue);
	}


	/**
	 * Return the object instance (Singleton)
	 *
	 * @return Environment The object instance
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The Environment class is now static.
	 */
	public static function getInstance()
	{
		@trigger_error('Using Environment::getInstance() has been deprecated and will no longer work in Contao 5.0. The Environment class is now static.', E_USER_DEPRECATED);

		if (static::$objInstance === null)
		{
			static::$objInstance = new static();
		}

		return static::$objInstance;
	}
}
