<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Automatically loads template files based on a mapper array
 *
 * The class stores template names and automatically loads the files upon their
 * first usage. It uses a mapper array to support complex nesting and arbitrary
 * subfolders to store the template files in.
 *
 * Usage:
 *
 *     ClassLoader::addFile('moo_mediabox', 'core/templates');
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TemplateLoader
{

	/**
	 * Known files
	 * @var array
	 */
	protected static $files = array();

	/**
	 * Add a new template with its file path
	 *
	 * @param string $name The template name
	 * @param string $file The path to the template folder
	 */
	public static function addFile($name, $file)
	{
		self::$files[$name] = $file;
	}

	/**
	 * Add multiple new templates with their file paths
	 *
	 * @param array $files An array of files
	 */
	public static function addFiles($files)
	{
		foreach ($files as $name=>$file)
		{
			self::addFile($name, $file);
		}
	}

	/**
	 * Return the template files as array
	 *
	 * @return array An array of files
	 */
	public static function getFiles()
	{
		return self::$files;
	}

	/**
	 * Return the files matching a prefix as array
	 *
	 * @param string $prefix The prefix (e.g. "moo_")
	 *
	 * @return array An array of matching files
	 */
	public static function getPrefixedFiles($prefix)
	{
		return array_values(preg_grep('/^' . rtrim($prefix, '_') . '($|_)/', array_keys(self::$files)));
	}

	/**
	 * Return a template path
	 *
	 * @param string $template The template name
	 * @param string $format   The output format (e.g. "html5")
	 * @param string $custom   The custom templates folder (defaults to "templates")
	 *
	 * @return string The path to the template file
	 */
	public static function getPath($template, $format, $custom='templates')
	{
		$file = $template . '.' . $format;
		$rootDir = \System::getContainer()->getParameter('kernel.project_dir');

		// Check the theme folder first
		if (file_exists($rootDir . '/' . $custom . '/' . $file))
		{
			return $rootDir . '/' . $custom . '/' . $file;
		}

		// Then check the global templates directory (see #5547)
		if ($custom != 'templates')
		{
			if (file_exists($rootDir . '/templates/' . $file))
			{
				return $rootDir . '/templates/' . $file;
			}
		}

		return static::getDefaultPath($template, $format);
	}

	/**
	 * Return the path to the default template
	 *
	 * @param string $template The template name
	 * @param string $format   The output format (e.g. "html5")
	 *
	 * @return string The path to the default template file
	 *
	 * @throws \Exception If $template does not exist
	 */
	public static function getDefaultPath($template, $format)
	{
		$file = $template . '.' . $format;
		$container = \System::getContainer();
		$rootDir = $container->getParameter('kernel.project_dir');

		if (isset(self::$files[$template]))
		{
			return $rootDir . '/' . self::$files[$template] . '/' . $file;
		}

		$strPath = null;

		try
		{
			// Search for the template if it is not in the lookup array (last match wins)
			foreach ($container->get('contao.resource_finder')->findIn('templates')->name($file) as $file)
			{
				/** @var SplFileInfo $file */
				$strPath = $file->getPathname();
			}
		}
		catch (\InvalidArgumentException $e) {}

		if ($strPath !== null)
		{
			return $strPath;
		}

		throw new \Exception('Could not find template "' . $template . '"');
	}

	/**
	 * Find the templates in the Contao resource folders.
	 */
	public static function initialize()
	{
		$objFilesystem = new Filesystem();
		$container = \System::getContainer();
		$strCacheDir = $container->getParameter('kernel.cache_dir');

		// Try to load from cache
		if (file_exists($strCacheDir . '/contao/config/templates.php'))
		{
			self::addFiles(include $strCacheDir . '/contao/config/templates.php');
		}
		else
		{
			try
			{
				foreach (\System::getContainer()->get('contao.resource_finder')->findIn('templates')->name('*.html5') as $file)
				{
					/** @var SplFileInfo $file */
					self::addFile($file->getBasename('.html5'), rtrim($objFilesystem->makePathRelative($file->getPath(), $container->getParameter('kernel.project_dir')), '/'));
				}
			}
			catch (\InvalidArgumentException $e) {}
		}
	}
}

class_alias(TemplateLoader::class, 'TemplateLoader');
