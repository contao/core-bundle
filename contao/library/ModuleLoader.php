<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Library
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao;

use Contao\Bundle\CoreBundle\HttpKernel\Bundle\ContaoLegacyBundle;


/**
 * Loads modules based on their autoload.ini configuration
 *
 * The class reads the autoload.ini files of the available modules and returns
 * an array of active modules with their dependencies solved.
 *
 * Usage:
 *
 *     $arrModules = ModuleLoader::getActive();
 *     $arrModules = ModuleLoader::getDisabled();
 *
 * @package   Library
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class ModuleLoader
{

	/**
	 * Active modules
	 * @var array
	 */
	protected static $active = [];

	/**
	 * Disabled modules
	 * @var array
	 */
	protected static $disabled = [];


	/**
	 * Return the active modules as array
	 *
	 * @return array An array of active modules
	 */
	public static function getActive()
	{
		if (empty(static::$active))
		{
			global $kernel;

			foreach ($kernel->getBundles() as $k=>$v)
			{
				// Only add the legacy bundles
				if ($v instanceof ContaoLegacyBundle)
				{
					static::$active[] = $v->getName();
				}
			}
		}

		return static::$active;
	}


	/**
	 * Return the disabled modules as array
	 *
	 * @return array An array of disabled modules
	 */
	public static function getDisabled()
	{
		return static::$disabled; # FIXME
	}
}
