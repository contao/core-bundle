<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

if (\PHP_VERSION_ID >= 70000)
{
	throw new \RuntimeException('The String class cannot be used in PHP ' . PHP_VERSION . '. Use the StringUtil class instead.');
}

@trigger_error('Using the String class has been deprecated and will no longer work in PHP 7. Use the StringUtil class instead.', E_USER_DEPRECATED);

/**
 * Provides a String class for backwards compatibility.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
 *             Use the StringUtil class instead.
 */
class String extends \StringUtil
{
	/**
	 * Object instance (Singleton)
	 * @var StringUtil
	 */
	protected static $objInstance;

	/**
	 * Prevent direct instantiation (Singleton)
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The String class is now static.
	 */
	protected function __construct()
	{
	}

	/**
	 * Prevent cloning of the object (Singleton)
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The String class is now static.
	 */
	final public function __clone()
	{
	}

	/**
	 * Return the object instance (Singleton)
	 *
	 * @return static The object instance
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             The String class is now static.
	 */
	public static function getInstance()
	{
		@trigger_error('Using String::getInstance() has been deprecated and will no longer work in Contao 5.0. The String class is now static.', E_USER_DEPRECATED);

		if (static::$objInstance === null)
		{
			static::$objInstance = new static();
		}

		return static::$objInstance;
	}
}
