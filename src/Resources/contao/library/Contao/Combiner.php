<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Leafo\ScssPhp\Compiler;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Combines .css or .js files into one single file
 *
 * Usage:
 *
 *     $combiner = new Combiner();
 *
 *     $combiner->add('css/style.css');
 *     $combiner->add('css/fonts.scss');
 *     $combiner->add('css/print.less');
 *
 *     echo $combiner->getCombinedFile();
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Combiner extends \System
{

	/**
	 * The .css file extension
	 * @var string
	 */
	const CSS = '.css';

	/**
	 * The .js file extension
	 * @var string
	 */
	const JS = '.js';

	/**
	 * The .scss file extension
	 * @var string
	 */
	const SCSS = '.scss';

	/**
	 * The .less file extension
	 * @var string
	 */
	const LESS = '.less';

	/**
	 * Unique file key
	 * @var string
	 */
	protected $strKey = '';

	/**
	 * Operation mode
	 * @var string
	 */
	protected $strMode;

	/**
	 * Files
	 * @var array
	 */
	protected $arrFiles = array();

	/**
	 * Web dir relative to TL_ROOT
	 * @var string
	 */
	protected $strWebDir;


	/**
	 * Public constructor required
	 */
	public function __construct()
	{
		$fs = new Filesystem();
		$container = \System::getContainer();
		$strWebDir = rtrim($fs->makePathRelative($container->getParameter('contao.web_dir'), TL_ROOT), '/');

		if (strncmp($strWebDir, '../', 3) === 0 || $strWebDir == '..')
		{
			throw new \RuntimeException(sprintf('Web dir "%s" is not inside TL_ROOT', $container->getParameter('contao.web_dir')));
		}

		$this->strWebDir = $strWebDir . '/';

		parent::__construct();
	}


	/**
	 * Add a file to the combined file
	 *
	 * @param string $strFile    The file to be added
	 * @param string $strVersion An optional version number
	 * @param string $strMedia   The media type of the file (.css only)
	 *
	 * @throws \InvalidArgumentException If $strFile is invalid
	 * @throws \LogicException           If different file types are mixed
	 */
	public function add($strFile, $strVersion=null, $strMedia='all')
	{
		$strType = strrchr($strFile, '.');

		// Check the file type
		if ($strType != self::CSS && $strType != self::JS && $strType != self::SCSS && $strType != self::LESS)
		{
			throw new \InvalidArgumentException("Invalid file $strFile");
		}

		$strMode = ($strType == self::JS) ? self::JS : self::CSS;

		// Set the operation mode
		if ($this->strMode === null)
		{
			$this->strMode = $strMode;
		}
		elseif ($this->strMode != $strMode)
		{
			throw new \LogicException('You cannot mix different file types. Create another Combiner object instead.');
		}

		// Check the source file
		if (!file_exists(TL_ROOT . '/' . $strFile))
		{
			// Handle public bundle resources in web/
			if (file_exists(TL_ROOT . '/' . $this->strWebDir . $strFile))
			{
				@trigger_error('Paths relative to the webdir are deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);
				$strFile = $this->strWebDir . $strFile;
			}
			else
			{
				return;
			}
		}

		// Prevent duplicates
		if (isset($this->arrFiles[$strFile]))
		{
			return;
		}

		// Default version
		if ($strVersion === null)
		{
			$strVersion = filemtime(TL_ROOT . '/' . $strFile);
		}

		// Store the file
		$arrFile = array
		(
			'name' => $strFile,
			'version' => $strVersion,
			'media' => $strMedia,
			'extension' => $strType
		);

		$this->arrFiles[$strFile] = $arrFile;
		$this->strKey .= '-f' . $strFile . '-v' . $strVersion . '-m' . $strMedia;
	}


	/**
	 * Add multiple files from an array
	 *
	 * @param array  $arrFiles   An array of files to be added
	 * @param string $strVersion An optional version number
	 * @param string $strMedia   The media type of the file (.css only)
	 */
	public function addMultiple(array $arrFiles, $strVersion=null, $strMedia='screen')
	{
		foreach ($arrFiles as $strFile)
		{
			$this->add($strFile, $strVersion, $strMedia);
		}
	}


	/**
	 * Check whether files have been added
	 *
	 * @return boolean True if there are files
	 */
	public function hasEntries()
	{
		return !empty($this->arrFiles);
	}


	/**
	 * Generates the files and returns the URLs.
	 *
	 * @return array The file URLs
	 */
	public function getFileUrls()
	{
		$return = array();
		$strTarget = substr($this->strMode, 1);

		foreach ($this->arrFiles as $arrFile)
		{
			$content = file_get_contents(TL_ROOT . '/' . $arrFile['name']);

			// Compile SCSS/LESS files into temporary files
			if ($arrFile['extension'] == self::SCSS || $arrFile['extension'] == self::LESS)
			{
				$strPath = 'assets/' . $strTarget . '/' . str_replace('/', '_', $arrFile['name']) . $this->strMode;

				$objFile = new \File($strPath);
				$objFile->write($this->handleScssLess($content, $arrFile));
				$objFile->close();

				$return[] = $strPath;
			}
			else
			{
				$name = $arrFile['name'];

				// Strip the web/ prefix (see #328)
				if (strncmp($name, $this->strWebDir, strlen($this->strWebDir)) === 0)
				{
					$name = substr($name, strlen($this->strWebDir));
				}

				// Add the media query (see #7070)
				if ($arrFile['media'] != '' && $arrFile['media'] != 'all' && strpos($content, '@media') === false)
				{
					$name .= '" media="' . $arrFile['media'];
				}

				$return[] = $name;
			}
		}

		return $return;
	}


	/**
	 * Generate the combined file and return its path
	 *
	 * @param string $strUrl An optional URL to prepend
	 *
	 * @return string The path to the combined file
	 */
	public function getCombinedFile($strUrl=null)
	{
		if (\Config::get('debugMode'))
		{
			return $this->getDebugMarkup();
		}

		return $this->getCombinedFileUrl($strUrl);
	}


	/**
	 * Generates the debug markup.
	 *
	 * @return string The debug markup
	 */
	protected function getDebugMarkup()
	{
		$return = $this->getFileUrls();

		if ($this->strMode == self::JS)
		{
			return implode('"></script><script src="', $return);
		}
		else
		{
			return implode('"><link rel="stylesheet" href="', $return);
		}
	}


	/**
	 * Generate the combined file and return its path
	 *
	 * @param string $strUrl An optional URL to prepend
	 *
	 * @return string The path to the combined file
	 */
	protected function getCombinedFileUrl($strUrl=null)
	{
		if ($strUrl === null)
		{
			$strUrl = TL_ASSETS_URL;
		}

		$strTarget = substr($this->strMode, 1);
		$strKey = substr(md5($this->strKey), 0, 12);

		// Load the existing file
		if (file_exists(TL_ROOT . '/assets/' . $strTarget . '/' . $strKey . $this->strMode))
		{
			return $strUrl . 'assets/' . $strTarget . '/' . $strKey . $this->strMode;
		}

		// Create the file
		$objFile = new \File('assets/' . $strTarget . '/' . $strKey . $this->strMode);
		$objFile->truncate();

		foreach ($this->arrFiles as $arrFile)
		{
			$content = file_get_contents(TL_ROOT . '/' . $arrFile['name']);

			// HOOK: modify the file content
			if (isset($GLOBALS['TL_HOOKS']['getCombinedFile']) && is_array($GLOBALS['TL_HOOKS']['getCombinedFile']))
			{
				foreach ($GLOBALS['TL_HOOKS']['getCombinedFile'] as $callback)
				{
					$this->import($callback[0]);
					$content = $this->{$callback[0]}->{$callback[1]}($content, $strKey, $this->strMode, $arrFile);
				}
			}

			if ($arrFile['extension'] == self::CSS)
			{
				$content = $this->handleCss($content, $arrFile);
			}
			elseif ($arrFile['extension'] == self::SCSS || $arrFile['extension'] == self::LESS)
			{
				$content = $this->handleScssLess($content, $arrFile);
			}

			$objFile->append($content);
		}

		unset($content);
		$objFile->close();

		// Create a gzipped version
		if (\Config::get('gzipScripts') && function_exists('gzencode'))
		{
			\File::putContent('assets/' . $strTarget . '/' . $strKey . $this->strMode . '.gz', gzencode(file_get_contents(TL_ROOT . '/assets/' . $strTarget . '/' . $strKey . $this->strMode), 9));
		}

		return $strUrl . 'assets/' . $strTarget . '/' . $strKey . $this->strMode;
	}


	/**
	 * Handle CSS files
	 *
	 * @param string $content The file content
	 * @param array  $arrFile The file array
	 *
	 * @return string The modified file content
	 */
	protected function handleCss($content, $arrFile)
	{
		$content = $this->fixPaths($content, $arrFile);

		// Add the media type if there is no @media command in the code
		if ($arrFile['media'] != '' && $arrFile['media'] != 'all' && strpos($content, '@media') === false)
		{
			$content = '@media ' . $arrFile['media'] . "{\n" . $content . "\n}";
		}

		return $content;
	}


	/**
	 * Handle SCSS/LESS files
	 *
	 * @param string $content The file content
	 * @param array  $arrFile The file array
	 *
	 * @return string The modified file content
	 */
	protected function handleScssLess($content, $arrFile)
	{
		if ($arrFile['extension'] == self::SCSS)
		{
			$objCompiler = new Compiler();

			$objCompiler->setImportPaths(array
			(
				TL_ROOT . '/' . dirname($arrFile['name']),
				TL_ROOT . '/vendor/contao-components/compass/css'
			));

			$objCompiler->setFormatter((\Config::get('debugMode') ? 'Leafo\ScssPhp\Formatter\Expanded' : 'Leafo\ScssPhp\Formatter\Compressed'));

			return $this->fixPaths($objCompiler->compile($content), $arrFile);
		}
		else
		{
			$strPath = dirname($arrFile['name']);

			$arrOptions = array
			(
				'strictMath' => true,
				'compress' => !\Config::get('debugMode'),
				'import_dirs' => array(TL_ROOT . '/' . $strPath => $strPath)
			);

			$objParser = new \Less_Parser();
			$objParser->SetOptions($arrOptions);
			$objParser->parse($content);

			return $this->fixPaths($objParser->getCss(), $arrFile);
		}
	}


	/**
	 * Fix the paths
	 *
	 * @param string $content The file content
	 * @param array  $arrFile The file array
	 *
	 * @return string The modified file content
	 */
	protected function fixPaths($content, $arrFile)
	{
		$strDirname = dirname($arrFile['name']);
		$strGlue = ($strDirname != '.') ? $strDirname . '/' : '';

		$strBuffer = '';
		$chunks = preg_split('/url\(["\']??(.+)["\']??\)/U', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

		// Check the URLs
		for ($i=0, $c=count($chunks); $i<$c; $i=$i+2)
		{
			$strBuffer .= $chunks[$i];

			if (!isset($chunks[$i+1]))
			{
				break;
			}

			$strData = $chunks[$i+1];

			// Skip absolute links and embedded images (see #5082)
			if (strncmp($strData, 'data:', 5) !== 0 && strncmp($strData, 'http://', 7) !== 0 && strncmp($strData, 'https://', 8) !== 0 && strncmp($strData, '/', 1) !== 0)
			{
				// Make the paths relative to the root (see #4161)
				if (strncmp($strData, '../', 3) !== 0)
				{
					$strData = '../../' . $strGlue . $strData;
				}
				else
				{
					$dir = $strDirname;

					// Remove relative paths
					while (strncmp($strData, '../', 3) === 0)
					{
						$dir = dirname($dir);
						$strData = substr($strData, 3);
					}

					$glue = ($dir != '.') ? $dir . '/' : '';
					$strData = '../../' . $glue . $strData;
				}
			}

			$strBuffer .= 'url("' . $strData . '")';
		}

		return $strBuffer;
	}
}
