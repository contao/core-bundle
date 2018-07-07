<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Picker\PickerInterface;
use Contao\Database\Result;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provide methods to manage back end controllers.
 *
 * @property Ajax $objAjax
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class Backend extends Controller
{

	/**
	 * Load the database object
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->import('Database');
		$this->setStaticUrls();
	}

	/**
	 * Return the current theme as string
	 *
	 * @return string The name of the theme
	 */
	public static function getTheme()
	{
		$theme = \Config::get('backendTheme');

		if ($theme != '' && $theme != 'flexible' && is_dir(TL_ROOT . '/system/themes/' . $theme))
		{
			return $theme;
		}

		return 'flexible';
	}

	/**
	 * Return the back end themes as array
	 *
	 * @return array An array of available back end themes
	 */
	public static function getThemes()
	{
		$arrReturn = array();
		$arrThemes = scan(TL_ROOT . '/system/themes');

		foreach ($arrThemes as $strTheme)
		{
			if (strncmp($strTheme, '.', 1) === 0 || !is_dir(TL_ROOT . '/system/themes/' . $strTheme))
			{
				continue;
			}

			$arrReturn[$strTheme] = $strTheme;
		}

		return $arrReturn;
	}

	/**
	 * Return the TinyMCE language
	 *
	 * @return string
	 */
	public static function getTinyMceLanguage()
	{
		$lang = $GLOBALS['TL_LANGUAGE'];

		if ($lang == '')
		{
			return 'en';
		}

		$lang = str_replace('-', '_', $lang);

		// The translation exists
		if (file_exists(TL_ROOT . '/assets/tinymce4/js/langs/' . $lang . '.js'))
		{
			return $lang;
		}

		if (($short = substr($GLOBALS['TL_LANGUAGE'], 0, 2)) != $lang)
		{
			// Try the short tag, e.g. "de" instead of "de_CH"
			if (file_exists(TL_ROOT . '/assets/tinymce4/js/langs/' . $short . '.js'))
			{
				return $short;
			}
		}
		elseif (($long = $short . '_' . strtoupper($short)) != $lang)
		{
			// Try the long tag, e.g. "fr_FR" instead of "fr" (see #6952)
			if (file_exists(TL_ROOT . '/assets/tinymce4/js/langs/' . $long . '.js'))
			{
				return $long;
			}
		}

		// Fallback to English
		return 'en';
	}

	/**
	 * Get the Ace code editor type from a file extension
	 *
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function getAceType($ext)
	{
		switch ($ext)
		{
			case 'css':
			case 'diff':
			case 'html':
			case 'ini':
			case 'java':
			case 'json':
			case 'less':
			case 'mysql':
			case 'php':
			case 'scss':
			case 'sql':
			case 'twig':
			case 'xml':
			case 'yaml':
				return $ext;
				break;

			case 'js':
			case 'javascript':
				return 'javascript';
				break;

			case 'md':
			case 'markdown':
				return 'markdown';
				break;

			case 'cgi':
			case 'pl':
				return 'perl';
				break;

			case 'py':
				return 'python';
				break;

			case 'txt':
				return 'text';
				break;

			case 'c': case 'cc': case 'cpp': case 'c++':
			case 'h': case 'hh': case 'hpp': case 'h++':
				return 'c_cpp';
				break;

			case 'html5':
			case 'xhtml':
				return 'php';
				break;

			case 'svg':
			case 'svgz':
				return 'xml';
				break;

			default:
				return 'text';
				break;
		}
	}

	/**
	 * Return a list of TinyMCE templates as JSON string
	 *
	 * @return string
	 */
	public static function getTinyTemplates()
	{
		$strDir = \Config::get('uploadPath') . '/tiny_templates';

		if (!is_dir(TL_ROOT . '/' . $strDir))
		{
			return '';
		}

		$arrFiles = array();
		$arrTemplates = scan(TL_ROOT . '/' . $strDir);

		foreach ($arrTemplates as $strFile)
		{
			if (strncmp('.', $strFile, 1) !== 0 && is_file(TL_ROOT . '/' . $strDir . '/' . $strFile))
			{
				$arrFiles[] = '{ title: "' . $strFile . '", url: "' . $strDir . '/' . $strFile . '" }';
			}
		}

		return implode(",\n", $arrFiles) . "\n";
	}

	/**
	 * Add the request token to the URL
	 *
	 * @param string  $strRequest
	 * @param boolean $blnAddRef
	 * @param array   $arrUnset
	 *
	 * @return string
	 */
	public static function addToUrl($strRequest, $blnAddRef=true, $arrUnset=array())
	{
		// Unset the "no back button" flag
		$arrUnset[] = 'nb';

		return parent::addToUrl($strRequest . (($strRequest != '') ? '&amp;' : '') . 'rt=' . REQUEST_TOKEN, $blnAddRef, $arrUnset);
	}

	/**
	 * Handle "runonce" files
	 *
	 * @throws \Exception
	 */
	public static function handleRunOnce()
	{
		try
		{
			$files = \System::getContainer()->get('contao.resource_locator')->locate('config/runonce.php', null, false);
		}
		catch (\InvalidArgumentException $e)
		{
			return;
		}

		foreach ($files as $file)
		{
			try
			{
				include $file;
			}
			catch (\Exception $e) {}

			$strRelpath = \StringUtil::stripRootDir($file);

			if (!unlink($file))
			{
				throw new \Exception("The file $strRelpath cannot be deleted. Please remove the file manually and correct the file permission settings on your server.");
			}

			\System::log("File $strRelpath ran once and has then been removed successfully", __METHOD__, TL_GENERAL);
		}
	}

	/**
	 * Open a back end module and return it as HTML
	 *
	 * @param string               $module
	 * @param PickerInterface|null $picker
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 */
	protected function getBackendModule($module, PickerInterface $picker = null)
	{
		$arrModule = array();

		foreach ($GLOBALS['BE_MOD'] as &$arrGroup)
		{
			if (isset($arrGroup[$module]))
			{
				$arrModule = &$arrGroup[$module];
				break;
			}
		}

		$this->import('BackendUser', 'User');
		$blnAccess = (isset($arrModule['disablePermissionChecks']) && $arrModule['disablePermissionChecks'] === true) || $this->User->hasAccess($module, 'modules');

		// Check whether the current user has access to the current module
		if (!$blnAccess)
		{
			throw new AccessDeniedException('Back end module "' . $module . '" is not allowed for user "' . $this->User->username . '".');
		}

		// The module does not exist
		if (empty($arrModule))
		{
			throw new \InvalidArgumentException('Back end module "' . $module . '" is not defined in the BE_MOD array');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$arrTables = (array) $arrModule['tables'];
		$strTable = \Input::get('table') ?: $arrTables[0];
		$id = (!\Input::get('act') && \Input::get('id')) ? \Input::get('id') : $objSession->get('CURRENT_ID');

		// Store the current ID in the current session
		if ($id != $objSession->get('CURRENT_ID'))
		{
			$objSession->set('CURRENT_ID', $id);
		}

		\define('CURRENT_ID', (\Input::get('table') ? $id : \Input::get('id')));
		$this->Template->headline = $GLOBALS['TL_LANG']['MOD'][$module][0];

		// Add the module style sheet
		if (isset($arrModule['stylesheet']))
		{
			foreach ((array) $arrModule['stylesheet'] as $stylesheet)
			{
				$GLOBALS['TL_CSS'][] = $stylesheet;
			}
		}

		// Add module javascript
		if (isset($arrModule['javascript']))
		{
			foreach ((array) $arrModule['javascript'] as $javascript)
			{
				$GLOBALS['TL_JAVASCRIPT'][] = $javascript;
			}
		}

		$dc = null;

		// Create the data container object
		if ($strTable != '')
		{
			if (!\in_array($strTable, $arrTables))
			{
				throw new AccessDeniedException('Table "' . $strTable . '" is not allowed in module "' . $module . '".');
			}

			// Load the language and DCA file
			\System::loadLanguageFile($strTable);
			$this->loadDataContainer($strTable);

			// Include all excluded fields which are allowed for the current user
			if ($GLOBALS['TL_DCA'][$strTable]['fields'])
			{
				foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k=>$v)
				{
					if ($v['exclude'])
					{
						if ($this->User->hasAccess($strTable.'::'.$k, 'alexf'))
						{
							if ($strTable == 'tl_user_group')
							{
								$GLOBALS['TL_DCA'][$strTable]['fields'][$k]['orig_exclude'] = $GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'];
							}

							$GLOBALS['TL_DCA'][$strTable]['fields'][$k]['exclude'] = false;
						}
					}
				}
			}

			// Fabricate a new data container object
			if ($GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'] == '')
			{
				$this->log('Missing data container for table "' . $strTable . '"', __METHOD__, TL_ERROR);
				trigger_error('Could not create a data container object', E_USER_ERROR);
			}

			$dataContainer = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];

			/** @var DataContainer $dc */
			$dc = new $dataContainer($strTable, $arrModule);

			if ($picker !== null && $dc instanceof DataContainer)
			{
				$dc->initPicker($picker);
			}
		}

		// Wrap the existing headline
		$this->Template->headline = '<span>' . $this->Template->headline . '</span>';

		// AJAX request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax->executePostActions($dc);
		}

		// Trigger the module callback
		elseif (class_exists($arrModule['callback']))
		{
			/** @var Module $objCallback */
			$objCallback = new $arrModule['callback']($dc);

			$this->Template->main .= $objCallback->generate();
		}

		// Custom action (if key is not defined in config.php the default action will be called)
		elseif (\Input::get('key') && isset($arrModule[\Input::get('key')]))
		{
			$objCallback = \System::importStatic($arrModule[\Input::get('key')][0]);
			$response = $objCallback->{$arrModule[\Input::get('key')][1]}($dc);

			if ($response instanceof RedirectResponse)
			{
				throw new ResponseException($response);
			}
			elseif ($response instanceof Response)
			{
				$response = $response->getContent();
			}

			$this->Template->main .= $response;

			// Add the name of the parent element
			if (isset($_GET['table']) && \in_array(\Input::get('table'), $arrTables) && \Input::get('table') != $arrTables[0])
			{
				if ($GLOBALS['TL_DCA'][$strTable]['config']['ptable'] != '')
				{
					$objRow = $this->Database->prepare("SELECT * FROM " . $GLOBALS['TL_DCA'][$strTable]['config']['ptable'] . " WHERE id=?")
											 ->limit(1)
											 ->execute(CURRENT_ID);

					if ($objRow->title != '')
					{
						$this->Template->headline .= ' › <span>' . $objRow->title . '</span>';
					}
					elseif ($objRow->name != '')
					{
						$this->Template->headline .= ' › <span>' . $objRow->name . '</span>';
					}
				}
			}

			// Add the name of the submodule
			$this->Template->headline .= ' › <span>' . sprintf($GLOBALS['TL_LANG'][$strTable][\Input::get('key')][1], \Input::get('id')) . '</span>';
		}

		// Default action
		elseif (\is_object($dc))
		{
			$act = \Input::get('act');

			if ($act == '' || $act == 'paste' || $act == 'select')
			{
				$act = ($dc instanceof \listable) ? 'showAll' : 'edit';
			}

			switch ($act)
			{
				case 'delete':
				case 'show':
				case 'showAll':
				case 'undo':
					if (!$dc instanceof \listable)
					{
						$this->log('Data container ' . $strTable . ' is not listable', __METHOD__, TL_ERROR);
						trigger_error('The current data container is not listable', E_USER_ERROR);
					}
					break;

				case 'create':
				case 'cut':
				case 'cutAll':
				case 'copy':
				case 'copyAll':
				case 'move':
				case 'edit':
					if (!$dc instanceof \editable)
					{
						$this->log('Data container ' . $strTable . ' is not editable', __METHOD__, TL_ERROR);
						trigger_error('The current data container is not editable', E_USER_ERROR);
					}
					break;
			}

			// Add the name of the parent elements
			if ($strTable && \in_array($strTable, $arrTables) && $strTable != $arrTables[0])
			{
				$trail = array();

				$pid = $dc->id;
				$table = $strTable;
				$ptable = (\Input::get('act') != 'edit') ? $GLOBALS['TL_DCA'][$strTable]['config']['ptable'] : $strTable;

				while ($ptable && !\in_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'], array(5, 6)))
				{
					$objRow = $this->Database->prepare("SELECT * FROM " . $ptable . " WHERE id=?")
											 ->limit(1)
											 ->execute($pid);

					// Add only parent tables to the trail
					if ($table != $ptable)
					{
						// Add table name
						if (isset($GLOBALS['TL_LANG']['MOD'][$table]))
						{
							$trail[] = ' › <span>'. $GLOBALS['TL_LANG']['MOD'][$table] . '</span>';
						}

						// Add object title or name
						if ($objRow->title != '')
						{
							$trail[] = ' › <span>' . $objRow->title . '</span>';
						}
						elseif ($objRow->name != '')
						{
							$trail[] = ' › <span>' . $objRow->name . '</span>';
						}
						elseif ($objRow->headline != '')
						{
							$trail[] = ' › <span>' . $objRow->headline . '</span>';
						}

					}

					$this->loadDataContainer($ptable);

					// Next parent table
					$pid = $objRow->pid;
					$table = $ptable;
					$ptable = ($GLOBALS['TL_DCA'][$ptable]['config']['dynamicPtable']) ? $objRow->ptable : $GLOBALS['TL_DCA'][$ptable]['config']['ptable'];
				}

				// Add the last parent table
				if (isset($GLOBALS['TL_LANG']['MOD'][$table]))
				{
					$trail[] = ' › <span>'. $GLOBALS['TL_LANG']['MOD'][$table] . '</span>';
				}

				// Add the breadcrumb trail in reverse order
				foreach (array_reverse($trail) as $breadcrumb)
				{
					$this->Template->headline .= $breadcrumb;
				}
			}

			// Add the current action
			if (\Input::get('act') == 'editAll')
			{
				if (isset($GLOBALS['TL_LANG']['MSC']['all'][0]))
				{
					$this->Template->headline .= ' › <span>' . $GLOBALS['TL_LANG']['MSC']['all'][0] . '</span>';
				}
			}
			elseif (\Input::get('act') == 'overrideAll')
			{
				if (isset($GLOBALS['TL_LANG']['MSC']['all_override'][0]))
				{
					$this->Template->headline .= ' › <span>' . $GLOBALS['TL_LANG']['MSC']['all_override'][0] . '</span>';
				}
			}
			else
			{
				if (\Input::get('id'))
				{
					if (\Input::get('do') == 'files' || \Input::get('do') == 'tpl_editor')
					{
						// Handle new folders (see #7980)
						if (strpos(\Input::get('id'), '__new__') !== false)
						{
							$this->Template->headline .= ' › <span>' . \dirname(\Input::get('id')) . '</span> › <span>' . $GLOBALS['TL_LANG'][$strTable]['new'][1] . '</span>';
						}
						else
						{
							$this->Template->headline .= ' › <span>' . \Input::get('id') . '</span>';
						}
					}
					elseif (isset($GLOBALS['TL_LANG'][$strTable][$act][1]))
					{
						$this->Template->headline .= ' › <span>' . sprintf($GLOBALS['TL_LANG'][$strTable][$act][1], \Input::get('id')) . '</span>';
					}
				}
				elseif (\Input::get('pid'))
				{
					if (\Input::get('do') == 'files' || \Input::get('do') == 'tpl_editor')
					{
						if (\Input::get('act') == 'move')
						{
							$this->Template->headline .= ' › <span>' . \Input::get('pid') . '</span> › <span>' . $GLOBALS['TL_LANG'][$strTable]['move'][1] . '</span>';
						}
						else
						{
							$this->Template->headline .= ' › <span>' . \Input::get('pid') . '</span>';
						}
					}
					elseif (isset($GLOBALS['TL_LANG'][$strTable][$act][1]))
					{
						$this->Template->headline .= ' › <span>' . sprintf($GLOBALS['TL_LANG'][$strTable][$act][1], \Input::get('pid')) . '</span>';
					}
				}
			}

			return $dc->$act();
		}

		return null;
	}

	/**
	 * Get all searchable pages and return them as array
	 *
	 * @param integer $pid
	 * @param string  $domain
	 * @param boolean $blnIsSitemap
	 *
	 * @return array
	 */
	public static function findSearchablePages($pid=0, $domain='', $blnIsSitemap=false)
	{
		$objPages = \PageModel::findPublishedByPid($pid, array('ignoreFePreview'=>true));

		if ($objPages === null)
		{
			return array();
		}

		$arrPages = array();

		// Recursively walk through all subpages
		foreach ($objPages as $objPage)
		{
			if ($objPage->type == 'regular')
			{
				// Searchable and not protected
				if ((!$objPage->noSearch || $blnIsSitemap) && (!$objPage->protected || (\Config::get('indexProtected') && (!$blnIsSitemap || $objPage->sitemap == 'map_always'))) && (!$blnIsSitemap || $objPage->sitemap != 'map_never') && !$objPage->requireItem)
				{
					$arrPages[] = $objPage->getAbsoluteUrl();

					// Get articles with teaser
					if (($objArticles = \ArticleModel::findPublishedWithTeaserByPid($objPage->id, array('ignoreFePreview'=>true))) !== null)
					{
						foreach ($objArticles as $objArticle)
						{
							$arrPages[] = $objPage->getAbsoluteUrl('/articles/' . ($objArticle->alias ?: $objArticle->id));
						}
					}
				}
			}

			// Get subpages
			if ((!$objPage->protected || \Config::get('indexProtected')) && ($arrSubpages = static::findSearchablePages($objPage->id, $domain, $blnIsSitemap)))
			{
				$arrPages = array_merge($arrPages, $arrSubpages);
			}
		}

		return $arrPages;
	}

	/**
	 * Add the file meta information to the request
	 *
	 * @param string  $strUuid
	 * @param string  $strPtable
	 * @param integer $intPid
	 *
	 * @deprecated Deprecated since Contao 4.4, to be removed in Contao 5.0.
	 */
	public static function addFileMetaInformationToRequest($strUuid, $strPtable, $intPid)
	{
		@trigger_error('Using Backend::addFileMetaInformationToRequest() has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

		$objFile = \FilesModel::findByUuid($strUuid);

		if ($objFile === null)
		{
			return;
		}

		$arrMeta = \StringUtil::deserialize($objFile->meta);

		if (empty($arrMeta))
		{
			return;
		}

		$objPage = null;

		if ($strPtable == 'tl_article')
		{
			$objPage = \PageModel::findOneBy(array('tl_page.id=(SELECT pid FROM tl_article WHERE id=?)'), $intPid);
		}
		else
		{
			// HOOK: support custom modules
			if (isset($GLOBALS['TL_HOOKS']['addFileMetaInformationToRequest']) && \is_array($GLOBALS['TL_HOOKS']['addFileMetaInformationToRequest']))
			{
				foreach ($GLOBALS['TL_HOOKS']['addFileMetaInformationToRequest'] as $callback)
				{
					if (($val = \System::importStatic($callback[0])->{$callback[1]}($strPtable, $intPid)) !== false)
					{
						$objPage = $val;
					}
				}

				if ($objPage instanceof Result && $objPage->numRows < 1)
				{
					return;
				}

				if (\is_object($objPage) && !($objPage instanceof PageModel))
				{
					$objPage = \PageModel::findByPk($objPage->id);
				}
			}
		}

		if ($objPage === null)
		{
			return;
		}

		$objPage->loadDetails();

		// Convert the language to a locale (see #5678)
		$strLanguage = str_replace('-', '_', $objPage->rootLanguage);

		if (isset($arrMeta[$strLanguage]))
		{
			if (!empty($arrMeta[$strLanguage]['title']) && \Input::post('title') == '')
			{
				\Input::setPost('title', $arrMeta[$strLanguage]['title']);
			}

			if (!empty($arrMeta[$strLanguage]['alt']) && \Input::post('alt') == '')
			{
				\Input::setPost('alt', $arrMeta[$strLanguage]['alt']);
			}

			if (!empty($arrMeta[$strLanguage]['caption']) && \Input::post('caption') == '')
			{
				\Input::setPost('caption', $arrMeta[$strLanguage]['caption']);
			}
		}
	}

	/**
	 * Add a breadcrumb menu to the page tree
	 *
	 * @param string $strKey
	 *
	 * @throws AccessDeniedException
	 * @throws \RuntimeException
	 */
	public static function addPagesBreadcrumb($strKey='tl_page_node')
	{
		/** @var AttributeBagInterface $objSession */
		$objSession = \System::getContainer()->get('session')->getBag('contao_backend');

		// Set a new node
		if (isset($_GET['pn']))
		{
			// Check the path (thanks to Arnaud Buchoux)
			if (\Validator::isInsecurePath(\Input::get('pn', true)))
			{
				throw new \RuntimeException('Insecure path ' . \Input::get('pn', true));
			}

			$objSession->set($strKey, \Input::get('pn', true));
			\Controller::redirect(preg_replace('/&pn=[^&]*/', '', \Environment::get('request')));
		}

		$intNode = $objSession->get($strKey);

		if ($intNode < 1)
		{
			return;
		}

		// Check the path (thanks to Arnaud Buchoux)
		if (\Validator::isInsecurePath($intNode))
		{
			throw new \RuntimeException('Insecure path ' . $intNode);
		}

		$arrIds   = array();
		$arrLinks = array();
		$objUser  = \BackendUser::getInstance();

		// Generate breadcrumb trail
		if ($intNode)
		{
			$intId = $intNode;
			$objDatabase = \Database::getInstance();

			do
			{
				$objPage = $objDatabase->prepare("SELECT * FROM tl_page WHERE id=?")
									   ->limit(1)
									   ->execute($intId);

				if ($objPage->numRows < 1)
				{
					// Currently selected page does not exist
					if ($intId == $intNode)
					{
						$objSession->set($strKey, 0);

						return;
					}

					break;
				}

				$arrIds[] = $intId;

				// No link for the active page
				if ($objPage->id == $intNode)
				{
					$arrLinks[] = \Backend::addPageIcon($objPage->row(), '', null, '', true) . ' ' . $objPage->title;
				}
				else
				{
					$arrLinks[] = \Backend::addPageIcon($objPage->row(), '', null, '', true) . ' <a href="' . \Backend::addToUrl('pn='.$objPage->id) . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'">' . $objPage->title . '</a>';
				}

				// Do not show the mounted pages
				if (!$objUser->isAdmin && $objUser->hasAccess($objPage->id, 'pagemounts'))
				{
					break;
				}

				$intId = $objPage->pid;
			}
			while ($intId > 0 && $objPage->type != 'root');
		}

		// Check whether the node is mounted
		if (!$objUser->hasAccess($arrIds, 'pagemounts'))
		{
			$objSession->set($strKey, 0);
			throw new AccessDeniedException('Page ID ' . $intNode . ' is not mounted.');
		}

		// Limit tree
		$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = array($intNode);

		// Add root link
		$arrLinks[] = \Image::getHtml('pagemounts.svg') . ' <a href="' . \Backend::addToUrl('pn=0') . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']).'">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';
		$arrLinks = array_reverse($arrLinks);

		// Insert breadcrumb menu
		$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['breadcrumb'] .= '

<nav aria-label="' . $GLOBALS['TL_LANG']['MSC']['breadcrumbMenu'] . '">
  <ul id="tl_breadcrumb">
    <li>' . implode(' › </li><li>', $arrLinks) . '</li>
  </ul>
</nav>';
	}

	/**
	 * Add an image to each page in the tree
	 *
	 * @param array         $row
	 * @param string        $label
	 * @param DataContainer $dc
	 * @param string        $imageAttribute
	 * @param boolean       $blnReturnImage
	 * @param boolean       $blnProtected
	 *
	 * @return string
	 */
	public static function addPageIcon($row, $label, DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		if ($blnProtected)
		{
			$row['protected'] = true;
		}

		$image = \Controller::getPageStatusIcon((object) $row);
		$imageAttribute = trim($imageAttribute . ' data-icon="' . \Controller::getPageStatusIcon((object) array_merge($row, array('published'=>'1'))) . '" data-icon-disabled="' . \Controller::getPageStatusIcon((object) array_merge($row, array('published'=>''))) . '"');

		// Return the image only
		if ($blnReturnImage)
		{
			return \Image::getHtml($image, '', $imageAttribute);
		}

		// Mark root pages
		if ($row['type'] == 'root' || \Input::get('do') == 'article')
		{
			$label = '<strong>' . $label . '</strong>';
		}

		// Add the breadcrumb link
		$label = '<a href="' . \Backend::addToUrl('pn='.$row['id']) . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'">' . $label . '</a>';

		// Return the image
		return '<a href="contao/main.php?do=feRedirect&amp;page='.$row['id'].'" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['view']).'"' . (($dc->table != 'tl_page') ? ' class="tl_gray"' : '') . ' target="_blank">'.\Image::getHtml($image, '', $imageAttribute).'</a> '.$label;
	}

	/**
	 * Return the system messages as HTML
	 *
	 * @return string The messages HTML markup
	 */
	public static function getSystemMessages()
	{
		$strMessages = '';

		// HOOK: add custom messages
		if (isset($GLOBALS['TL_HOOKS']['getSystemMessages']) && \is_array($GLOBALS['TL_HOOKS']['getSystemMessages']))
		{
			$arrMessages = array();

			foreach ($GLOBALS['TL_HOOKS']['getSystemMessages'] as $callback)
			{
				$strBuffer = \System::importStatic($callback[0])->{$callback[1]}();

				if ($strBuffer != '')
				{
					$arrMessages[] = $strBuffer;
				}
			}

			if (!empty($arrMessages))
			{
				$strMessages .= implode("\n", $arrMessages);
			}
		}

		return $strMessages;
	}

	/**
	 * Add a breadcrumb menu to the file tree
	 *
	 * @param string $strKey
	 *
	 * @throws AccessDeniedException
	 * @throws \RuntimeException
	 */
	public static function addFilesBreadcrumb($strKey='tl_files_node')
	{
		/** @var AttributeBagInterface $objSession */
		$objSession = \System::getContainer()->get('session')->getBag('contao_backend');

		// Set a new node
		if (isset($_GET['fn']))
		{
			// Check the path (thanks to Arnaud Buchoux)
			if (\Validator::isInsecurePath(\Input::get('fn', true)))
			{
				throw new \RuntimeException('Insecure path ' . \Input::get('fn', true));
			}

			$objSession->set($strKey, \Input::get('fn', true));
			\Controller::redirect(preg_replace('/(&|\?)fn=[^&]*/', '', \Environment::get('request')));
		}

		$strNode = $objSession->get($strKey);

		if ($strNode == '')
		{
			return;
		}

		// Check the path (thanks to Arnaud Buchoux)
		if (\Validator::isInsecurePath($strNode))
		{
			throw new \RuntimeException('Insecure path ' . $strNode);
		}

		// Currently selected folder does not exist
		if (!is_dir(TL_ROOT . '/' . $strNode))
		{
			$objSession->set($strKey, '');

			return;
		}

		$objUser  = \BackendUser::getInstance();
		$strPath  = \Config::get('uploadPath');
		$arrNodes = explode('/', preg_replace('/^' . preg_quote(\Config::get('uploadPath'), '/') . '\//', '', $strNode));
		$arrLinks = array();

		// Add root link
		$arrLinks[] = \Image::getHtml('filemounts.svg') . ' <a href="' . \Backend::addToUrl('fn=') . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectAllNodes']).'">' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</a>';

		// Generate breadcrumb trail
		foreach ($arrNodes as $strFolder)
		{
			$strPath .= '/' . $strFolder;

			// Do not show pages which are not mounted
			if (!$objUser->hasAccess($strPath, 'filemounts'))
			{
				continue;
			}

			// No link for the active folder
			if ($strPath == $strNode)
			{
				$arrLinks[] = \Image::getHtml('folderC.svg') . ' ' . $strFolder;
			}
			else
			{
				$arrLinks[] = \Image::getHtml('folderC.svg') . ' <a href="' . \Backend::addToUrl('fn='.$strPath) . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'">' . $strFolder . '</a>';
			}
		}

		// Check whether the node is mounted
		if (!$objUser->hasAccess($strNode, 'filemounts'))
		{
			$objSession->set($strKey, '');
			throw new AccessDeniedException('Folder ID "' . $strNode . '" is not mounted');
		}

		// Limit tree
		$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = array($strNode);

		// Insert breadcrumb menu
		$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'] .= '

<nav aria-label="' . $GLOBALS['TL_LANG']['MSC']['breadcrumbMenu'] . '">
  <ul id="tl_breadcrumb">
    <li>' . implode(' › </li><li>', $arrLinks) . '</li>
  </ul>
</nav>';
	}

	/**
	 * Convert an array of layout section IDs to an associative array with IDs and labels
	 *
	 * @param array $arrSections
	 *
	 * @return array
	 */
	public static function convertLayoutSectionIdsToAssociativeArray($arrSections)
	{
		$arrSections = array_flip(array_values(array_unique($arrSections)));

		foreach (array_keys($arrSections) as $k)
		{
			$arrSections[$k] = $GLOBALS['TL_LANG']['COLS'][$k];
		}

		asort($arrSections);

		return $arrSections;
	}

	/**
	 * Generate the DCA picker wizard
	 *
	 * @param boolean|array $extras
	 * @param string        $table
	 * @param string        $field
	 * @param string        $inputName
	 *
	 * @return string
	 */
	public static function getDcaPickerWizard($extras, $table, $field, $inputName)
	{
		$context = 'link';
		$extras = \is_array($extras) ? $extras : array();
		$providers = (isset($extras['providers']) && \is_array($extras['providers'])) ? $extras['providers'] : null;

		if (isset($extras['context']))
		{
			$context = $extras['context'];
			unset($extras['context']);
		}

		$factory = \System::getContainer()->get('contao.picker.builder');

		if (!$factory->supportsContext($context, $providers))
		{
			return '';
		}

		return ' <a href="' . ampersand($factory->getUrl($context, $extras)) . '" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" id="pp_' . $inputName . '">' . \Image::getHtml((\is_array($extras) && isset($extras['icon']) ? $extras['icon'] : 'pickpage.svg'), $GLOBALS['TL_LANG']['MSC']['pagepicker']) . '</a>
  <script>
    $("pp_' . $inputName . '").addEvent("click", function(e) {
      e.preventDefault();
      Backend.openModalSelector({
        "id": "tl_listing",
        "title": ' . json_encode($GLOBALS['TL_DCA'][$table]['fields'][$field]['label'][0]) . ',
        "url": this.href + "&value=" + document.getElementById("ctrl_' . $inputName . '").value,
        "callback": function(picker, value) {
          $("ctrl_' . $inputName . '").value = value.join(",");
        }.bind(this)
      });
    });
  </script>';
	}

	/**
	 * Add the custom layout section references
	 */
	public function addCustomLayoutSectionReferences()
	{
		$objLayout = $this->Database->getInstance()->query("SELECT sections FROM tl_layout WHERE sections!=''");

		while ($objLayout->next())
		{
			$arrCustom = \StringUtil::deserialize($objLayout->sections);

			// Add the custom layout sections
			if (!empty($arrCustom) && \is_array($arrCustom))
			{
				foreach ($arrCustom as $v)
				{
					if (!empty($v['id']))
					{
						$GLOBALS['TL_LANG']['COLS'][$v['id']] = $v['title'];
					}
				}
			}
		}
	}

	/**
	 * Get all allowed pages and return them as string
	 *
	 * @return string
	 */
	public function createPageList()
	{
		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return $this->doCreatePageList();
		}

		$return = '';
		$processed = array();

		foreach ($this->eliminateNestedPages($this->User->pagemounts) as $page)
		{
			$objPage = \PageModel::findWithDetails($page);

			// Root page mounted
			if ($objPage->type == 'root')
			{
				$title = $objPage->title;
				$start = $objPage->id;
			}

			// Regular page mounted
			else
			{
				$title = $objPage->rootTitle;
				$start = $objPage->rootId;
			}

			// Do not process twice
			if (\in_array($start, $processed))
			{
				continue;
			}

			// Skip websites that run under a different domain (see #2387)
			if ($objPage->domain && $objPage->domain != \Environment::get('host'))
			{
				continue;
			}

			$processed[] = $start;
			$return .= '<optgroup label="' . $title . '">' . $this->doCreatePageList($start) . '</optgroup>';
		}

		return $return;
	}

	/**
	 * Recursively get all allowed pages and return them as string
	 *
	 * @param integer $intId
	 * @param integer $level
	 *
	 * @return string
	 */
	protected function doCreatePageList($intId=0, $level=-1)
	{
		$objPages = $this->Database->prepare("SELECT id, title, type, dns FROM tl_page WHERE pid=? ORDER BY sorting")
								   ->execute($intId);

		if ($objPages->numRows < 1)
		{
			return '';
		}

		++$level;
		$strOptions = '';

		while ($objPages->next())
		{
			if ($objPages->type == 'root')
			{
				// Skip websites that run under a different domain
				if ($objPages->dns && $objPages->dns != \Environment::get('host'))
				{
					continue;
				}

				$strOptions .= '<optgroup label="' . $objPages->title . '">';
				$strOptions .= $this->doCreatePageList($objPages->id, -1);
				$strOptions .= '</optgroup>';
			}
			else
			{
				$strOptions .= sprintf('<option value="{{link_url::%s}}"%s>%s%s</option>', $objPages->id, (('{{link_url::' . $objPages->id . '}}' == \Input::get('value')) ? ' selected="selected"' : ''), str_repeat(' &nbsp; &nbsp; ', $level), \StringUtil::specialchars($objPages->title));
				$strOptions .= $this->doCreatePageList($objPages->id, $level);
			}
		}

		return $strOptions;
	}

	/**
	 * Get all allowed files and return them as string
	 *
	 * @param string  $strFilter
	 * @param boolean $filemount
	 *
	 * @return string
	 */
	public function createFileList($strFilter='', $filemount=false)
	{
		// Deprecated since Contao 4.0, to be removed in Contao 5.0
		if ($strFilter === true)
		{
			@trigger_error('Passing "true" to Backend::createFileList() has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

			$strFilter = 'gif,jpg,jpeg,png';
		}

		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return $this->doCreateFileList(\Config::get('uploadPath'), -1, $strFilter);
		}

		$return = '';
		$processed = array();

		// Set custom filemount
		if ($filemount)
		{
			$this->User->filemounts = array($filemount);
		}

		// Limit nodes to the filemounts of the user
		foreach ($this->eliminateNestedPaths($this->User->filemounts) as $path)
		{
			if (\in_array($path, $processed))
			{
				continue;
			}

			$processed[] = $path;
			$return .= $this->doCreateFileList($path, -1, $strFilter);
		}

		return $return;
	}

	/**
	 * Recursively get all allowed files and return them as string
	 *
	 * @param string  $strFolder
	 * @param integer $level
	 * @param string  $strFilter
	 *
	 * @return string
	 */
	protected function doCreateFileList($strFolder=null, $level=-1, $strFilter='')
	{
		// Deprecated since Contao 4.0, to be removed in Contao 5.0
		if ($strFilter === true)
		{
			@trigger_error('Passing "true" to Backend::doCreateFileList() has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

			$strFilter = 'gif,jpg,jpeg,png';
		}

		$arrPages = scan(TL_ROOT . '/' . $strFolder);

		// Empty folder
		if (empty($arrPages))
		{
			return '';
		}

		// Protected folder
		if (\in_array('.htaccess', $arrPages))
		{
			return '';
		}

		++$level;
		$strFolders = '';
		$strFiles = '';

		// Recursively list all files and folders
		foreach ($arrPages as $strFile)
		{
			if (strncmp($strFile, '.', 1) === 0)
			{
				continue;
			}

			// Folders
			if (is_dir(TL_ROOT . '/' . $strFolder . '/' . $strFile))
			{
				$strFolders .=  $this->doCreateFileList($strFolder . '/' . $strFile, $level, $strFilter);
			}

			// Files
			else
			{
				// Filter images
				if ($strFilter != '' && !preg_match('/\.(' . str_replace(',', '|', $strFilter) . ')$/i', $strFile))
				{
					continue;
				}

				$strFiles .= sprintf('<option value="%s"%s>%s</option>', $strFolder . '/' . $strFile, (($strFolder . '/' . $strFile == \Input::get('value')) ? ' selected="selected"' : ''), \StringUtil::specialchars($strFile));
			}
		}

		if (\strlen($strFiles))
		{
			return '<optgroup label="' . \StringUtil::specialchars($strFolder) . '">' . $strFiles . $strFolders . '</optgroup>';
		}

		return $strFiles . $strFolders;
	}
}

class_alias(Backend::class, 'Backend');
