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


/**
 * Automatically loads class files based on a mapper array
 *
 * The class stores namespaces and classes and automatically loads the class
 * files upon their first usage. It uses a mapper array to support complex
 * nesting and arbitrary subfolders to store the class files in.
 *
 * Usage:
 *
 *     ClassLoader::addNamespace('Custom');
 *     ClassLoader::addClass('Custom\\Calendar', 'calendar/Calendar.php');
 *
 * @package   Library
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class ClassLoader
{

	/**
	 * Known namespaces
	 * @var array
	 */
	protected static $namespaces = array
	(
		'Contao'
	);

	/**
	 * Known classes
	 * @var array
	 */
	protected static $classes = array
	(
		'Contao\Automator'                 => 'vendor/contao/contao-library/src/Contao/Automator.php',
		'Contao\Cache'                     => 'vendor/contao/contao-library/src/Contao/Cache.php',
		'Contao\ClassLoader'               => 'vendor/contao/contao-library/src/Contao/ClassLoader.php',
		'Contao\Combiner'                  => 'vendor/contao/contao-library/src/Contao/Combiner.php',
		'Contao\Config'                    => 'vendor/contao/contao-library/src/Contao/Config.php',
		'Contao\Controller'                => 'vendor/contao/contao-library/src/Contao/Controller.php',
		'Contao\Database\Installer'        => 'vendor/contao/contao-library/src/Contao/Database/Installer.php',
		'Contao\Database\Mysql\Result'     => 'vendor/contao/contao-library/src/Contao/Database/Mysql/Result.php',
		'Contao\Database\Mysql\Statement'  => 'vendor/contao/contao-library/src/Contao/Database/Mysql/Statement.php',
		'Contao\Database\Mysql'            => 'vendor/contao/contao-library/src/Contao/Database/Mysql.php',
		'Contao\Database\Mysqli\Result'    => 'vendor/contao/contao-library/src/Contao/Database/Mysqli/Result.php',
		'Contao\Database\Mysqli\Statement' => 'vendor/contao/contao-library/src/Contao/Database/Mysqli/Statement.php',
		'Contao\Database\Mysqli'           => 'vendor/contao/contao-library/src/Contao/Database/Mysqli.php',
		'Contao\Database\Result'           => 'vendor/contao/contao-library/src/Contao/Database/Result.php',
		'Contao\Database\Statement'        => 'vendor/contao/contao-library/src/Contao/Database/Statement.php',
		'Contao\Database\Updater'          => 'vendor/contao/contao-library/src/Contao/Database/Updater.php',
		'Contao\Database'                  => 'vendor/contao/contao-library/src/Contao/Database.php',
		'Contao\Date'                      => 'vendor/contao/contao-library/src/Contao/Date.php',
		'Contao\Dbafs\Filter'              => 'vendor/contao/contao-library/src/Contao/Dbafs/Filter.php',
		'Contao\Dbafs'                     => 'vendor/contao/contao-library/src/Contao/Dbafs.php',
		'Contao\DcaExtractor'              => 'vendor/contao/contao-library/src/Contao/DcaExtractor.php',
		'Contao\DcaLoader'                 => 'vendor/contao/contao-library/src/Contao/DcaLoader.php',
		'Contao\Email'                     => 'vendor/contao/contao-library/src/Contao/Email.php',
		'Contao\Encryption'                => 'vendor/contao/contao-library/src/Contao/Encryption.php',
		'Contao\Environment'               => 'vendor/contao/contao-library/src/Contao/Environment.php',
		'Contao\Feed'                      => 'vendor/contao/contao-library/src/Contao/Feed.php',
		'Contao\FeedItem'                  => 'vendor/contao/contao-library/src/Contao/FeedItem.php',
		'Contao\File'                      => 'vendor/contao/contao-library/src/Contao/File.php',
		'Contao\Files'                     => 'vendor/contao/contao-library/src/Contao/Files.php',
		'Contao\Folder'                    => 'vendor/contao/contao-library/src/Contao/Folder.php',
		'Contao\Idna'                      => 'vendor/contao/contao-library/src/Contao/Idna.php',
		'Contao\Image'                     => 'vendor/contao/contao-library/src/Contao/Image.php',
		'Contao\Input'                     => 'vendor/contao/contao-library/src/Contao/Input.php',
		'Contao\Message'                   => 'vendor/contao/contao-library/src/Contao/Message.php',
		'Contao\Model\Collection'          => 'vendor/contao/contao-library/src/Contao/Model/Collection.php',
		'Contao\Model\QueryBuilder'        => 'vendor/contao/contao-library/src/Contao/Model/QueryBuilder.php',
		'Contao\Model\Registry'            => 'vendor/contao/contao-library/src/Contao/Model/Registry.php',
		'Contao\Model'                     => 'vendor/contao/contao-library/src/Contao/Model.php',
		'Contao\ModuleLoader'              => 'vendor/contao/contao-library/src/Contao/ModuleLoader.php',
		'Contao\Pagination'                => 'vendor/contao/contao-library/src/Contao/Pagination.php',
		'Contao\Request'                   => 'vendor/contao/contao-library/src/Contao/Request.php',
		'Contao\RequestToken'              => 'vendor/contao/contao-library/src/Contao/RequestToken.php',
		'Contao\Search'                    => 'vendor/contao/contao-library/src/Contao/Search.php',
		'Contao\Session'                   => 'vendor/contao/contao-library/src/Contao/Session.php',
		'Contao\SortedIterator'            => 'vendor/contao/contao-library/src/Contao/SortedIterator.php',
		'Contao\String'                    => 'vendor/contao/contao-library/src/Contao/String.php',
		'Contao\System'                    => 'vendor/contao/contao-library/src/Contao/System.php',
		'Contao\Template'                  => 'vendor/contao/contao-library/src/Contao/Template.php',
		'Contao\TemplateLoader'            => 'vendor/contao/contao-library/src/Contao/TemplateLoader.php',
		'Contao\User'                      => 'vendor/contao/contao-library/src/Contao/User.php',
		'Contao\Validator'                 => 'vendor/contao/contao-library/src/Contao/Validator.php',
		'Contao\View'                      => 'vendor/contao/contao-library/src/Contao/View.php',
		'Contao\Widget'                    => 'vendor/contao/contao-library/src/Contao/Widget.php',
		'Contao\ZipReader'                 => 'vendor/contao/contao-library/src/Contao/ZipReader.php',
		'Contao\ZipWriter'                 => 'vendor/contao/contao-library/src/Contao/ZipWriter.php'
	);


	/**
	 * Add a new namespace
	 *
	 * @param string $name The namespace name
	 */
	public static function addNamespace($name)
	{
		if (in_array($name, self::$namespaces))
		{
			return;
		}

		array_unshift(self::$namespaces, $name);
	}


	/**
	 * Add multiple new namespaces
	 *
	 * @param array $names An array of namespace names
	 */
	public static function addNamespaces($names)
	{
		foreach ($names as $name)
		{
			self::addNamespace($name);
		}
	}


	/**
	 * Return the namespaces as array
	 *
	 * @return array An array of all namespaces
	 */
	public static function getNamespaces()
	{
		return self::$namespaces;
	}


	/**
	 * Add a new class with its file path
	 *
	 * @param string $class The class name
	 * @param string $file  The path to the class file
	 */
	public static function addClass($class, $file)
	{
		self::$classes[$class] = $file;
	}


	/**
	 * Add multiple new classes with their file paths
	 *
	 * @param array $classes An array of classes
	 */
	public static function addClasses($classes)
	{
		foreach ($classes as $class=>$file)
		{
			self::addClass($class, $file);
		}
	}


	/**
	 * Return the classes as array.
	 *
	 * @return array An array of all classes
	 */
	public static function getClasses()
	{
		return self::$classes;
	}


	/**
	 * Autoload a class and create an alias in the global namespace
	 *
	 * To preserve backwards compatibility with Contao 2 extensions, all core
	 * classes will be aliased into the global namespace.
	 *
	 * @param string $class The class name
	 */
	public static function load($class)
	{
		if (class_exists($class, false) || interface_exists($class, false))
		{
			return;
		}

		// The class file is set in the mapper
		if (isset(self::$classes[$class]))
		{
			if (\Config::get('debugMode'))
			{
				$GLOBALS['TL_DEBUG']['classes_set'][] = $class;
			}

			include TL_ROOT . '/' . self::$classes[$class];
		}

		// Find the class in the registered namespaces
		elseif (($namespaced = self::findClass($class)) != false)
		{
			if (!class_exists($namespaced, false))
			{
				if (\Config::get('debugMode'))
				{
					$GLOBALS['TL_DEBUG']['classes_aliased'][] = $class . ' <span style="color:#999">(' . $namespaced . ')</span>';
				}

				include TL_ROOT . '/' . self::$classes[$namespaced];
			}

			class_alias($namespaced, $class);
		}

		// Pass the request to other autoloaders (e.g. Swift)
	}


	/**
	 * Search the namespaces for a matching entry
	 *
	 * @param string $class The class name
	 *
	 * @return string The full path including the namespace
	 */
	protected static function findClass($class)
	{
		foreach (self::$namespaces as $namespace)
		{
			if (isset(self::$classes[$namespace . '\\' . $class]))
			{
				return $namespace . '\\' . $class;
			}
		}

		return '';
	}


	/**
	 * Register the autoloader
	 */
	public static function register()
	{
		spl_autoload_register('ClassLoader::load');
	}


	/**
	 * Scan the module directories for config/autoload.php files and then
	 * register the autoloader on the SPL stack
	 */
	public static function scanAndRegister()
	{
		$strCacheFile = 'system/cache/config/autoload.php';

		// Try to load from cache
		if (!\Config::get('bypassCache') && file_exists(TL_ROOT . '/' . $strCacheFile))
		{
			include TL_ROOT . '/' . $strCacheFile;
		}
		else
		{
			foreach (\ModuleLoader::getActive() as $module)
			{
				$file = 'system/modules/' . $module . '/config/autoload.php';

				if (file_exists(TL_ROOT . '/' . $file))
				{
					include TL_ROOT . '/' . $file;
				}
			}
		}

		self::register();
	}
}
