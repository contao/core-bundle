<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Util\SymlinkUtil;
use Contao\Image\ResizeConfiguration;
use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * Provide methods to modify the file system.
 *
 * @property string  $path
 * @property string  $extension
 * @property boolean $createNewVersion
 * @property boolean $isDbAssisted
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class DC_Folder extends \DataContainer implements \listable, \editable
{

	/**
	 * Current path
	 * @var string
	 */
	protected $strPath;

	/**
	 * Current file extension
	 * @var string
	 */
	protected $strExtension;

	/**
	 * Current filemounts
	 * @var array
	 */
	protected $arrFilemounts = array();

	/**
	 * Valid file types
	 * @var array
	 */
	protected $arrValidFileTypes = array();

	/**
	 * Messages
	 * @var array
	 */
	protected $arrMessages = array();

	/**
	 * Counts
	 * @var array
	 */
	protected $arrCounts = array();

	/**
	 * True if a new version has to be created
	 * @var boolean
	 */
	protected $blnCreateNewVersion = false;

	/**
	 * Database assisted
	 * @var boolean
	 */
	protected $blnIsDbAssisted = false;


	/**
	 * Initialize the object
	 *
	 * @param string $strTable
	 *
	 * @throws AccessDeniedException
	 */
	public function __construct($strTable)
	{
		parent::__construct();

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Check the request token (see #4007)
		if (isset($_GET['act']))
		{
			if (!isset($_GET['rt']) || !\RequestToken::validate(\Input::get('rt')))
			{
				$objSession->set('INVALID_TOKEN_URL', \Environment::get('request'));
				$this->redirect('contao/confirm.php');
			}
		}

		$this->intId = \Input::get('id', true);

		// Clear the clipboard
		if (isset($_GET['clipboard']))
		{
			$objSession->set('CLIPBOARD', array());
			$this->redirect($this->getReferer());
		}

		// Check whether the table is defined
		if ($strTable == '' || !isset($GLOBALS['TL_DCA'][$strTable]))
		{
			$this->log('Could not load data container configuration for "' . $strTable . '"', __METHOD__, TL_ERROR);
			trigger_error('Could not load data container configuration', E_USER_ERROR);
		}

		// Check permission to create new folders
		if (\Input::get('act') == 'paste' && \Input::get('mode') == 'create' && isset($GLOBALS['TL_DCA'][$strTable]['list']['new']))
		{
			throw new AccessDeniedException('Attempt to create a new folder although the method has been overwritten in the data container.');
		}

		// Set IDs and redirect
		if (\Input::post('FORM_SUBMIT') == 'tl_select')
		{
			$ids = \Input::post('IDS');

			if (empty($ids) || !is_array($ids))
			{
				$this->reload();
			}

			// Decode the values (see #5764)
			$ids = array_map('rawurldecode', $ids);

			$session = $objSession->all();
			$session['CURRENT']['IDS'] = $ids;
			$objSession->replace($session);

			if (isset($_POST['edit']))
			{
				$this->redirect(str_replace('act=select', 'act=editAll', \Environment::get('request')));
			}
			elseif (isset($_POST['delete']))
			{
				$this->redirect(str_replace('act=select', 'act=deleteAll', \Environment::get('request')));
			}
			elseif (isset($_POST['cut']) || isset($_POST['copy']))
			{
				$arrClipboard = $objSession->get('CLIPBOARD');

				$arrClipboard[$strTable] = array
				(
					'id' => $ids,
					'mode' => (isset($_POST['cut']) ? 'cutAll' : 'copyAll')
				);

				$objSession->set('CLIPBOARD', $arrClipboard);
				$this->redirect($this->getReferer());
			}
		}

		$this->strTable = $strTable;
		$this->blnIsDbAssisted = $GLOBALS['TL_DCA'][$strTable]['config']['databaseAssisted'];

		// Check for valid file types
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['validFileTypes'])
		{
			$this->arrValidFileTypes = \StringUtil::trimsplit(',', strtolower($GLOBALS['TL_DCA'][$this->strTable]['config']['validFileTypes']));
		}

		$this->initPicker();

		// Call onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onload_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($this);
				}
				elseif (is_callable($callback))
				{
					$callback($this);
				}
			}
		}

		// Get all filemounts (root folders)
		if (is_array($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))
		{
			$this->arrFilemounts = $this->eliminateNestedPaths($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']);
		}
	}


	/**
	 * Set an object property
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'createNewVersion':
				$this->blnCreateNewVersion = (bool) $varValue;
				break;

			default;
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Return an object property
	 *
	 * @param string $strKey
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'path':
				return $this->strPath;
				break;

			case 'extension':
				return $this->strExtension;
				break;

			case 'createNewVersion':
				return $this->blnCreateNewVersion;
				break;

			case 'isDbAssisted':
				return $this->blnIsDbAssisted;
				break;
		}

		return parent::__get($strKey);
	}


	/**
	 * List all files and folders of the file system
	 *
	 * @return string
	 */
	public function showAll()
	{
		$return = '';

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = $objSession->getBag('contao_backend');

		$session = $objSessionBag->all();

		// Add to clipboard
		if (\Input::get('act') == 'paste')
		{
			if (\Input::get('mode') != 'create' && \Input::get('mode') != 'move')
			{
				$this->isValid($this->intId);
			}

			$arrClipboard = $objSession->get('CLIPBOARD');

			$arrClipboard[$this->strTable] = array
			(
				'id' => $this->urlEncode($this->intId),
				'childs' => \Input::get('childs'),
				'mode' => \Input::get('mode')
			);

			$objSession->set('CLIPBOARD', $arrClipboard);
		}

		// Get the session data and toggle the nodes
		if (\Input::get('tg') == 'all')
		{
			// Expand tree
			if (!is_array($session['filetree']) || empty($session['filetree']) || current($session['filetree']) != 1)
			{
				$session['filetree'] = $this->getMD5Folders(\Config::get('uploadPath'));
			}
			// Collapse tree
			else
			{
				$session['filetree'] = array();
			}

			$objSessionBag->replace($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)tg=[^& ]*/i', '', \Environment::get('request')));
		}

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');

		// Check clipboard
		if (!empty($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$this->import('Files');
		$this->import('BackendUser', 'User');

		$arrFound = array();
		$for = $session['search'][$this->strTable]['value'];

		// Limit the results by modifying $this->arrFilemounts
		if ($for != '')
		{
			// Wrap in a try catch block in case the regular expression is invalid (see #7743)
			try
			{
				$strPattern = "CAST(name AS CHAR) REGEXP ?";

				if (substr(\Config::get('dbCollation'), -3) == '_ci')
				{
					$strPattern = "LOWER(CAST(name AS CHAR)) REGEXP LOWER(?)";
				}

				if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields']['name']['foreignKey']))
				{
					list($t, $f) = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields']['name']['foreignKey']);

					$objRoot = $this->Database->prepare("SELECT path, type, extension FROM {$this->strTable} WHERE (" . $strPattern . " OR " . sprintf($strPattern, "(SELECT $f FROM $t WHERE $t.id={$this->strTable}.name)") . ") GROUP BY path")
											  ->execute($for, $for);
				}
				else
				{
					$objRoot = $this->Database->prepare("SELECT path, type, extension FROM {$this->strTable} WHERE " . $strPattern . " GROUP BY path")
											  ->execute($for);
				}

				if ($objRoot->numRows < 1)
				{
					$this->arrFilemounts = array();
				}
				else
				{
					$arrRoot = array();

					// Respect existing limitations (root IDs)
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']))
					{
						while ($objRoot->next())
						{
							foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'] as $root)
							{
								if (strncmp($root . '/', $objRoot->path . '/', strlen($root) + 1) === 0)
								{
									if ($objRoot->type == 'folder' || empty($this->arrValidFileTypes) || in_array($objRoot->extension, $this->arrValidFileTypes))
									{
										$arrFound[] = $objRoot->path;
									}

									$arrRoot[] = ($objRoot->type == 'folder') ? $objRoot->path : dirname($objRoot->path);
									continue(2);
								}
							}
						}
					}
					else
					{
						while ($objRoot->next())
						{
							if ($objRoot->type == 'folder' || empty($this->arrValidFileTypes) || in_array($objRoot->extension, $this->arrValidFileTypes))
							{
								$arrFound[] = $objRoot->path;
							}

							$arrRoot[] = ($objRoot->type == 'folder') ? $objRoot->path : dirname($objRoot->path);
						}
					}

					$this->arrFilemounts = $this->eliminateNestedPaths(array_unique($arrRoot));
				}
			}
			catch (\Exception $e) {}
		}

		// Call recursive function tree()
		if (empty($this->arrFilemounts) && !is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'] !== false)
		{
			$return .= $this->generateTree(TL_ROOT . '/' . \Config::get('uploadPath'), 0, false, true, ($blnClipboard ? $arrClipboard : false), $arrFound);
		}
		else
		{
			for ($i=0, $c=count($this->arrFilemounts); $i<$c; $i++)
			{
				if ($this->arrFilemounts[$i] != '' && is_dir(TL_ROOT . '/' . $this->arrFilemounts[$i]))
				{
					$return .= $this->generateTree(TL_ROOT . '/' . $this->arrFilemounts[$i], 0, true, true, ($blnClipboard ? $arrClipboard : false), $arrFound);
				}
			}
		}

		// Check for the "create new" button
		$clsNew = 'header_new_folder';
		$lblNew = $GLOBALS['TL_LANG'][$this->strTable]['new'][0];
		$ttlNew = $GLOBALS['TL_LANG'][$this->strTable]['new'][1];
		$hrfNew = 'act=paste&amp;mode=create';

		if (isset($GLOBALS['TL_DCA'][$this->strTable]['list']['new']))
		{
			$clsNew = $GLOBALS['TL_DCA'][$this->strTable]['list']['new']['class'];
			$lblNew = $GLOBALS['TL_DCA'][$this->strTable]['list']['new']['label'][0];
			$ttlNew = $GLOBALS['TL_DCA'][$this->strTable]['list']['new']['label'][1];
			$hrfNew = $GLOBALS['TL_DCA'][$this->strTable]['list']['new']['href'];
		}

		$imagePasteInto = \Image::getHtml('pasteinto.svg', $GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]);

		// Build the tree
		$return = $this->panel() . '
<div id="tl_buttons">'.((\Input::get('act') == 'select') ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : '') . ((\Input::get('act') != 'select' && !$blnClipboard && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? '
<a href="'.$this->addToUrl($hrfNew).'" class="'.$clsNew.'" title="'.\StringUtil::specialchars($ttlNew).'" accesskey="n" onclick="Backend.getScrollOffset()">'.$lblNew.'</a>
<a href="'.$this->addToUrl('&amp;act=paste&amp;mode=move').'" class="header_new" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['move'][1]).'" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG'][$this->strTable]['move'][0].'</a> ' : '') . ($blnClipboard ? '
<a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a> ' : $this->generateGlobalButtons()) . '
</div>' . \Message::generate() . ((\Input::get('act') == 'select') ? '

<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_select" class="tl_form'.((\Input::get('act') == 'select') ? ' unselectable' : '').'" method="post" novalidate>
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').(($session['search'][$this->strTable]['value'] != '') ? '

<div class="tl_message">
 <p class="tl_info">'.$GLOBALS['TL_LANG']['MSC']['searchExclude'].'</p>
</div>' : '').($blnClipboard ? '

<div id="paste_hint">
  <p>'.$GLOBALS['TL_LANG']['MSC']['selectNewPosition'].'</p>
</div>' : '').'

<div class="tl_listing_container tree_view" id="tl_listing">'.(isset($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['breadcrumb'] : '').((\Input::get('act') == 'select' || ($this->strPickerField && $GLOBALS['TL_DCA'][$this->strPickerTable]['fields'][$this->strPickerField]['eval']['fieldType'] == 'checkbox')) ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '').'

<ul class="tl_listing'.($this->strPickerField ? ' picker unselectable' : '').'"'.$this->getPickerAttributes().'>
  <li class="tl_folder_top cf"><div class="tl_left">'.\Image::getHtml('filemounts.svg').' '.$GLOBALS['TL_LANG']['MSC']['filetree'].'</div> <div class="tl_right">'.(($blnClipboard && empty($this->arrFilemounts) && !is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'] !== false) ? '<a href="'.$this->addToUrl('&amp;act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.\Config::get('uploadPath').(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1]).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a>' : '&nbsp;').'</div></li>'.$return.'
</ul>

</div>';

		// Close the form
		if (\Input::get('act') == 'select')
		{
			// Submit buttons
			$arrButtons = array();

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
			{
				$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['editSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
			{
				$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirmFile'].'\')">'.$GLOBALS['TL_LANG']['MSC']['deleteSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
			{
				$arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['moveSelected'].'</button>';
			}

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
			{
				$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG']['MSC']['copySelected'].'</button>';
			}

			// Call the buttons_callback (see #4691)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
					}
					elseif (is_callable($callback))
					{
						$arrButtons = $callback($arrButtons, $this);
					}
				}
			}

			if (count($arrButtons) < 3)
			{
				$strButtons = implode(' ', $arrButtons);
			}
			else
			{
				$strButtons = array_shift($arrButtons) . ' ';
				$strButtons .= '<div class="split-button">';
				$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<li>' . $strButton . '</li>';
				}

				$strButtons .= '</ul></div>';
			}

			$return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">
  ' . $strButtons . '
</div>

</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * Automatically switch to showAll
	 *
	 * @return string
	 */
	public function show()
	{
		return $this->showAll();
	}


	/**
	 * Create a new folder
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function create()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not creatable.');
		}

		$this->import('Files');
		$strFolder = \Input::get('pid', true);

		if ($strFolder == '' || !file_exists(TL_ROOT . '/' . $strFolder) || !$this->isMounted($strFolder))
		{
			throw new AccessDeniedException('Folder "' . $strFolder . '" is not mounted or is not a directory.');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Empty clipboard
		$arrClipboard = $objSession->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$objSession->set('CLIPBOARD', $arrClipboard);

		$this->Files->mkdir($strFolder . '/__new__');
		$this->redirect(html_entity_decode($this->switchToEdit($this->urlEncode($strFolder) . '/__new__')));
	}


	/**
	 * Move an existing file or folder
	 *
	 * @param string $source
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function cut($source=null)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not sortable.');
		}

		$strFolder = \Input::get('pid', true);
		$blnDoNotRedirect = ($source !== null);

		if ($source === null)
		{
			$source = $this->intId;
		}

		$this->isValid($source);

		if (!file_exists(TL_ROOT . '/' . $source) || !$this->isMounted($source))
		{
			throw new AccessDeniedException('File or folder "' . $source . '" is not mounted or cannot be found.');
		}

		if (!file_exists(TL_ROOT . '/' . $strFolder) || !$this->isMounted($strFolder))
		{
			throw new AccessDeniedException('Parent folder "' . $strFolder . '" is not mounted or is not a directory.');
		}

		// Avoid a circular reference
		if (preg_match('/^' . preg_quote($source, '/') . '/i', $strFolder))
		{
			throw new InternalServerErrorException('Attempt to move the folder "' . $source . '" to "' . $strFolder . '" (circular reference).');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Empty clipboard
		$arrClipboard = $objSession->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$objSession->set('CLIPBOARD', $arrClipboard);

		$this->import('Files');

		// Calculate the destination path
		$destination = str_replace(dirname($source), $strFolder, $source);

		// Do not move if the target exists and would be overriden (not possible for folders anyway)
		if (file_exists(TL_ROOT . '/' . $destination))
		{
			\Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetarget'], basename($source), dirname($destination)));
		}
		else
		{
			$this->Files->rename($source, $destination);

			// Update the database AFTER the file has been moved
			if ($this->blnIsDbAssisted)
			{
				$syncSource = \Dbafs::shouldBeSynchronized($source);
				$syncTarget = \Dbafs::shouldBeSynchronized($destination);

				if ($syncSource && $syncTarget)
				{
					\Dbafs::moveResource($source, $destination);
				}
				elseif ($syncSource)
				{
					\Dbafs::deleteResource($source);
				}
				elseif ($syncTarget)
				{
					\Dbafs::addResource($destination);
				}
			}

			// Call the oncut_callback
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}($source, $destination, $this);
					}
					elseif (is_callable($callback))
					{
						$callback($source, $destination, $this);
					}
				}
			}

			// Add a log entry
			$this->log('File or folder "'.$source.'" has been moved to "'.$destination.'"', __METHOD__, TL_FILES);
		}

		// Redirect
		if (!$blnDoNotRedirect)
		{
			$this->redirect($this->getReferer());
		}
	}


	/**
	 * Move all selected files and folders
	 *
	 * @throws InternalServerErrorException
	 */
	public function cutAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not sortable.');
		}

		// PID is mandatory
		if (!strlen(\Input::get('pid', true)))
		{
			$this->redirect($this->getReferer());
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$arrClipboard = $objSession->get('CLIPBOARD');

		if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id']))
		{
			foreach ($arrClipboard[$this->strTable]['id'] as $id)
			{
				$this->cut($id); // do not urldecode() here (see #6840)
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Recursively duplicate files and folders
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function copy($source=null, $destination=null)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not copyable.');
		}

		$strFolder = \Input::get('pid', true);
		$blnDoNotRedirect = ($source !== null);

		if ($source === null)
		{
			$source = $this->intId;
		}

		if ($destination === null)
		{
			$destination = str_replace(dirname($source), $strFolder, $source);
		}

		$this->isValid($source);
		$this->isValid($destination);

		if (!file_exists(TL_ROOT . '/' . $source) || !$this->isMounted($source))
		{
			throw new AccessDeniedException('File or folder "' . $source . '" is not mounted or cannot be found.');
		}

		if (!file_exists(TL_ROOT . '/' . $strFolder) || !$this->isMounted($strFolder))
		{
			throw new AccessDeniedException('Parent folder "' . $strFolder . '" is not mounted or is not a directory.');
		}

		// Avoid a circular reference
		if (preg_match('/^' . preg_quote($source, '/') . '/i', $strFolder))
		{
			throw new InternalServerErrorException('Attempt to copy the folder "' . $source . '" to "' . $strFolder . '" (circular reference).');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Empty clipboard
		$arrClipboard = $objSession->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$objSession->set('CLIPBOARD', $arrClipboard);

		$this->import('Files');

		// Copy folders
		if (is_dir(TL_ROOT . '/' . $source))
		{
			$count = 1;
			$new = $destination;

			// Add a suffix if the folder exists
			while (is_dir(TL_ROOT . '/' . $new) && $count < 12)
			{
				$new = $destination . '_' . $count++;
			}

			$destination = $new;
			$this->Files->rcopy($source, $destination);
		}

		// Copy a file
		else
		{
			$count = 1;
			$new = $destination;
			$ext = strtolower(substr($destination, strrpos($destination, '.') + 1));

			// Add a suffix if the file exists
			while (file_exists(TL_ROOT . '/' . $new) && $count < 12)
			{
				$new = str_replace('.' . $ext, '_' . $count++ . '.' . $ext, $destination);
			}

			$destination = $new;
			$this->Files->copy($source, $destination);
		}

		// Update the database AFTER the file has been copied
		if ($this->blnIsDbAssisted)
		{
			$syncSource = \Dbafs::shouldBeSynchronized($source);
			$syncTarget = \Dbafs::shouldBeSynchronized($destination);

			if ($syncSource && $syncTarget)
			{
				\Dbafs::copyResource($source, $destination);
			}
			elseif ($syncTarget)
			{
				\Dbafs::addResource($destination);
			}
		}

		// Call the oncopy_callback
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncopy_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncopy_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($source, $destination, $this);
				}
				elseif (is_callable($callback))
				{
					$callback($source, $destination, $this);
				}
			}
		}

		// Add a log entry
		$this->log('File or folder "'.$source.'" has been copied to "'.$destination.'"', __METHOD__, TL_FILES);

		// Redirect
		if (!$blnDoNotRedirect)
		{
			// Switch to edit mode
			if (is_file(TL_ROOT . '/' . $destination))
			{
				$this->redirect($this->switchToEdit($destination));
			}

			$this->redirect($this->getReferer());
		}
	}


	/**
	 * Move all selected files and folders
	 *
	 * @throws InternalServerErrorException
	 */
	public function copyAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not copyable.');
		}

		// PID is mandatory
		if (!strlen(\Input::get('pid', true)))
		{
			$this->redirect($this->getReferer());
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$arrClipboard = $objSession->get('CLIPBOARD');

		if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id']))
		{
			foreach ($arrClipboard[$this->strTable]['id'] as $id)
			{
				$this->copy($id); // do not urldecode() here (see #6840)
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Recursively delete files and folders
	 *
	 * @param string $source
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function delete($source=null)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not deletable.');
		}

		$blnDoNotRedirect = ($source !== null);

		if ($source === null)
		{
			$source = $this->intId;
		}

		$this->isValid($source);

		// Delete the file or folder
		if (!file_exists(TL_ROOT . '/' . $source) || !$this->isMounted($source))
		{
			throw new AccessDeniedException('File or folder "' . $source . '" is not mounted or cannot be found.');
		}

		// Call the ondelete_callback
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['ondelete_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['ondelete_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($source, $this);
				}
				elseif (is_callable($callback))
				{
					$callback($source, $this);
				}
			}
		}

		$this->import('Files');

		// Delete the folder or file
		if (is_dir(TL_ROOT . '/' . $source))
		{
			$this->Files->rrdir($source);

			// Also delete the symlink (see #710)
			if (is_link(TL_ROOT . '/web/' . $source))
			{
				$this->Files->delete('web/' . $source);
			}
		}
		else
		{
			$this->Files->delete($source);
		}

		// Update the database AFTER the resource has been deleted
		if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($source))
		{
			\Dbafs::deleteResource($source);
		}

		// Add a log entry
		$this->log('File or folder "' . $source . '" has been deleted', __METHOD__, TL_FILES);

		// Redirect
		if (!$blnDoNotRedirect)
		{
			$this->redirect($this->getReferer());
		}
	}


	/**
	 * Delete all files and folders that are currently shown
	 *
	 * @throws InternalServerErrorException
	 */
	public function deleteAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not deletable.');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$session = $objSession->all();
		$ids = $session['CURRENT']['IDS'];

		if (is_array($ids) && strlen($ids[0]))
		{
			foreach ($ids as $id)
			{
				$this->delete($id); // do not urldecode() here (see #6840)
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Automatically switch to showAll
	 *
	 * @return string
	 */
	public function undo()
	{
		return $this->showAll();
	}


	/**
	 * Move one or more local files to the server
	 *
	 * @param boolean $blnIsAjax
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 */
	public function move($blnIsAjax=false)
	{
		$strFolder = \Input::get('pid', true);

		if (!file_exists(TL_ROOT . '/' . $strFolder) || !$this->isMounted($strFolder))
		{
			throw new AccessDeniedException('Folder "' . $strFolder . '" is not mounted or is not a directory.');
		}

		if (!preg_match('/^'.preg_quote(\Config::get('uploadPath'), '/').'/i', $strFolder))
		{
			throw new AccessDeniedException('Parent folder "' . $strFolder . '" is not within the files directory.');
		}

		// Empty clipboard
		if (!$blnIsAjax)
		{
			/** @var SessionInterface $objSession */
			$objSession = \System::getContainer()->get('session');

			$arrClipboard = $objSession->get('CLIPBOARD');
			$arrClipboard[$this->strTable] = array();
			$objSession->set('CLIPBOARD', $arrClipboard);
		}

		// Instantiate the uploader
		$this->import('BackendUser', 'User');
		$class = $this->User->uploader;

		// See #4086
		if (!class_exists($class))
		{
			$class = 'DropZone';
		}

		/** @var FileUpload $objUploader */
		$objUploader = new $class();

		// Process the uploaded files
		if (\Input::post('FORM_SUBMIT') == 'tl_upload')
		{
			// Generate the DB entries
			if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($strFolder))
			{
				// Upload the files
				$arrUploaded = $objUploader->uploadTo($strFolder);

				if (empty($arrUploaded) && !$objUploader->hasError())
				{
					if ($blnIsAjax)
					{
						throw new ResponseException(new Response($GLOBALS['TL_LANG']['ERR']['emptyUpload'], 400));
					}

					\Message::addError($GLOBALS['TL_LANG']['ERR']['emptyUpload']);
					$this->reload();
				}

				foreach ($arrUploaded as $strFile)
				{
					\Dbafs::addResource($strFile);
				}
			}
			else
			{
				// Not DB-assisted, so just upload the file
				$arrUploaded = $objUploader->uploadTo($strFolder);
			}

			// HOOK: post upload callback
			if (isset($GLOBALS['TL_HOOKS']['postUpload']) && is_array($GLOBALS['TL_HOOKS']['postUpload']))
			{
				foreach ($GLOBALS['TL_HOOKS']['postUpload'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}($arrUploaded);
					}
					elseif (is_callable($callback))
					{
						$callback($arrUploaded);
					}
				}
			}

			// Update the hash of the target folder
			if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($strFolder))
			{
				\Dbafs::updateFolderHashes($strFolder);
			}

			// Redirect or reload
			if (!$objUploader->hasError())
			{
				if ($blnIsAjax)
				{
					throw new ResponseException(new Response(\Message::generateUnwrapped(), 201));
				}

				// Do not purge the html folder (see #2898)
				if (isset($_POST['uploadNback']) && !$objUploader->hasResized())
				{
					\Message::reset();
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		}

		// Submit buttons
		$arrButtons = array();
		$arrButtons['upload'] = '<button type="submit" name="upload" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG'][$this->strTable]['upload'].'</button>';
		$arrButtons['uploadNback'] = '<button type="submit" name="uploadNback" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG'][$this->strTable]['uploadNback'].'</button>';

		// Call the buttons_callback (see #4691)
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
				}
				elseif (is_callable($callback))
				{
					$arrButtons = $callback($arrButtons, $this);
				}
			}
		}

		if (count($arrButtons) < 3)
		{
			$strButtons = implode(' ', $arrButtons);
		}
		else
		{
			$strButtons = array_shift($arrButtons) . ' ';
			$strButtons .= '<div class="split-button">';
			$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

			foreach ($arrButtons as $strButton)
			{
				$strButtons .= '<li>' . $strButton . '</li>';
			}

			$strButtons .= '</ul></div>';
		}

		// Display the upload form
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post"'.(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').' enctype="multipart/form-data">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_upload">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="MAX_FILE_SIZE" value="'.\Config::get('maxFileSize').'">

<div class="tl_tbox">
  <div class="widget">
    <h3>'.$GLOBALS['TL_LANG'][$this->strTable]['fileupload'][0].'</h3>'.$objUploader->generateMarkup().'
  </div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . $strButtons . '
</div>

</div>

</form>';
	}


	/**
	 * Auto-generate a form to rename a file or folder
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 */
	public function edit()
	{
		$return = '';
		$this->noReload = false;
		$this->isValid($this->intId);

		if (!file_exists(TL_ROOT . '/' . $this->intId) || !$this->isMounted($this->intId))
		{
			throw new AccessDeniedException('File or folder "' . $this->intId . '" is not mounted or cannot be found.');
		}

		$objModel = null;
		$objVersions = null;

		// Add the versioning routines
		if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($this->intId))
		{
			if (stristr($this->intId, '__new__') === false)
			{
				$objModel = \FilesModel::findByPath($this->intId);

				if ($objModel === null)
				{
					$objModel = \Dbafs::addResource($this->intId);
				}

				$this->objActiveRecord = $objModel;
			}

			$this->blnCreateNewVersion = false;

			/** @var FilesModel $objModel */
			$objVersions = new \Versions($this->strTable, $objModel->id);

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'])
			{
				// Compare versions
				if (\Input::get('versions'))
				{
					$objVersions->compare();
				}

				// Restore a version
				if (\Input::post('FORM_SUBMIT') == 'tl_version' && \Input::post('version') != '')
				{
					$objVersions->restore(\Input::post('version'));
					$this->reload();
				}
			}

			$objVersions->initialize();
		}
		else
		{
			// Unset the database fields
			$GLOBALS['TL_DCA'][$this->strTable]['fields'] = array_intersect_key($GLOBALS['TL_DCA'][$this->strTable]['fields'], array('name' => true, 'protected' => true));
		}

		// Build an array from boxes and rows (do not show excluded fields)
		$this->strPalette = $this->getPalette();
		$boxes = \StringUtil::trimsplit(';', $this->strPalette);

		if (!empty($boxes))
		{
			// Get fields
			foreach ($boxes as $k=>$v)
			{
				$boxes[$k] = \StringUtil::trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]))
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (empty($boxes[$k]))
				{
					unset($boxes[$k]);
				}
			}

			// Render boxes
			$class = 'tl_tbox';

			foreach ($boxes as $v)
			{
				$return .= '
<div class="'.$class.' cf">';

				// Build rows of the current box
				foreach ($v as $vv)
				{
					$this->strField = $vv;
					$this->strInputName = $vv;

					// Load the current value
					if ($vv == 'name')
					{
						$objFile = is_dir(TL_ROOT . '/' . $this->intId) ? new \Folder($this->intId) : new \File($this->intId);

						$this->strPath = \StringUtil::stripRootDir($objFile->dirname);
						$this->strExtension = ($objFile->origext != '') ? '.'.$objFile->origext : '';
						$this->varValue = $objFile->filename;

						// Fix hidden Unix system files
						if (strncmp($this->varValue, '.', 1) === 0)
						{
							$this->strExtension = '';
						}

						// Clear the current value if it is a new folder
						if (\Input::post('FORM_SUBMIT') != 'tl_files' && \Input::post('FORM_SUBMIT') != 'tl_templates' && $this->varValue == '__new__')
						{
							$this->varValue = '';
						}
					}
					else
					{
						$this->varValue = ($objModel !== null) ? $objModel->$vv : null;
					}

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);
							}
							elseif (is_callable($callback))
							{
								$this->varValue = $callback($this->varValue, $this);
							}
						}
					}

					// Build row
					$return .= $this->row();
				}

				$class = 'tl_box';
				$return .= '
  <input type="hidden" name="FORM_FIELDS[]" value="'.\StringUtil::specialchars($this->strPalette).'">
</div>';
			}
		}

		// Versions overview
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] && $this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($this->intId))
		{
			$version = $objVersions->renderDropdown();
		}
		else
		{
			$version = '';
		}

		// Submit buttons
		$arrButtons = array();
		$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['save'].'</button>';

		if (!\Input::get('nb'))
		{
			$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG']['MSC']['saveNclose'].'</button>';
		}

		// Call the buttons_callback (see #4691)
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
				}
				elseif (is_callable($callback))
				{
					$arrButtons = $callback($arrButtons, $this);
				}
			}
		}

		if (count($arrButtons) < 3)
		{
			$strButtons = implode(' ', $arrButtons);
		}
		else
		{
			$strButtons = array_shift($arrButtons) . ' ';
			$strButtons .= '<div class="split-button">';
			$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

			foreach ($arrButtons as $strButton)
			{
				$strButtons .= '<li>' . $strButton . '</li>';
			}

			$strButtons .= '</ul></div>';
		}

		// Add the buttons and end the form
		$return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . $strButtons . '
</div>

</div>
</form>';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = $version . '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post"'.(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;

		// Reload the page to prevent _POST variables from being sent twice
		if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
		{
			// Trigger the onsubmit_callback
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$this->{$callback[0]}->{$callback[1]}($this);
					}
					elseif (is_callable($callback))
					{
						$callback($this);
					}
				}
			}

			// Save the current version
			if ($this->blnCreateNewVersion && $objModel !== null)
			{
				$objVersions->create();

				// Call the onversion_callback
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback']))
				{
					@trigger_error('Using the onversion_callback has been deprecated and will no longer work in Contao 5.0. Use the oncreate_version_callback instead.', E_USER_DEPRECATED);

					foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$this->{$callback[0]}->{$callback[1]}($this->strTable, $objModel->id, $this);
						}
						elseif (is_callable($callback))
						{
							$callback($this->strTable, $objModel->id, $this);
						}
					}
				}
			}

			// Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
			if ($this->blnIsDbAssisted && $objModel !== null)
			{
				$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
							   ->execute(time(), $objModel->id);
			}

			// Redirect
			if (isset($_POST['saveNclose']))
			{
				\Message::reset();
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);
				$this->redirect($this->getReferer());
			}

			// Reload
			if ($this->blnIsDbAssisted && $this->objActiveRecord !== null)
			{
				$this->redirect($this->addToUrl('id='.$this->urlEncode($this->objActiveRecord->path)));
			}
			else
			{
				$this->redirect($this->addToUrl('id='.$this->urlEncode($this->strPath.'/'.$this->varValue).$this->strExtension));
			}
		}

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '

<script>
  window.addEvent(\'domready\', function() {
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
  });
</script>';
		}

		return $return;
	}


	/**
	 * Auto-generate a form to edit all records that are currently shown
	 *
	 * @return string
	 *
	 * @throws InternalServerErrorException
	 */
	public function editAll()
	{
		$return = '';

		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			throw new InternalServerErrorException('Table "' . $this->strTable . '" is not editable.');
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		// Get current IDs from session
		$session = $objSession->all();
		$ids = $session['CURRENT']['IDS'];

		// Save field selection in session
		if (\Input::post('FORM_SUBMIT') == $this->strTable.'_all' && \Input::get('fields'))
		{
			$session['CURRENT'][$this->strTable] = \Input::post('all_fields');
			$objSession->replace($session);
		}

		$fields = $session['CURRENT'][$this->strTable];

		// Add fields
		if (!empty($fields) && is_array($fields) && \Input::get('fields'))
		{
			$class = 'tl_tbox';

			// Walk through each record
			foreach ($ids as $id)
			{
				$this->intId = $id;
				$this->strPalette = \StringUtil::trimsplit('[;,]', $this->getPalette());

				$objModel = null;
				$objVersions = null;

				// Get the DB entry
				if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($id))
				{
					$objModel = \FilesModel::findByPath($id);

					if ($objModel === null)
					{
						$objModel = \Dbafs::addResource($id);
					}

					$this->objActiveRecord = $objModel;
					$this->blnCreateNewVersion = false;

					/** @var FilesModel $objModel */
					$objVersions = new \Versions($this->strTable, $objModel->id);
					$objVersions->initialize();
				}
				else
				{
					// Unset the database fields
					$this->strPalette = array_filter($this->strPalette, function ($val) { return $val == 'name' || $val == 'protected'; });
				}

				$return .= '
<div class="'.$class.'">';

				$class = 'tl_box';
				$formFields = array();
				$strHash = md5($id);

				foreach ($this->strPalette as $v)
				{
					// Check whether field is excluded
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['exclude'])
					{
						continue;
					}

					if (!in_array($v, $fields))
					{
						continue;
					}

					$this->strField = $v;
					$this->strInputName = $v.'_'.$strHash;
					$formFields[] = $v.'_'.$strHash;

					// Load the current value
					if ($v == 'name')
					{
						$objFile = is_dir(TL_ROOT . '/' . $id) ? new \Folder($id) : new \File($id);

						$this->strPath = \StringUtil::stripRootDir($objFile->dirname);
						$this->strExtension = ($objFile->origext != '') ? '.'.$objFile->origext : '';
						$this->varValue = $objFile->filename;

						// Fix hidden Unix system files
						if (strncmp($this->varValue, '.', 1) === 0)
						{
							$this->strExtension = '';
						}
					}
					else
					{
						$this->varValue = ($objModel !== null) ? $objModel->$v : null;
					}

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);
							}
							elseif (is_callable($callback))
							{
								$this->varValue = $callback($this->varValue, $this);
							}
						}
					}

					// Build the current row
					$return .= $this->row();
				}

				// Close box
				$return .= '
  <input type="hidden" name="FORM_FIELDS_'.$strHash.'[]" value="'.\StringUtil::specialchars(implode(',', $formFields)).'">
</div>';

				// Save the record
				if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
				{
					// Call onsubmit_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->{$callback[0]}->{$callback[1]}($this);
							}
							elseif (is_callable($callback))
							{
								$callback($this);
							}
						}
					}

					// Create a new version
					if ($this->blnCreateNewVersion && $objModel !== null)
					{
						$objVersions->create();

						// Call the onversion_callback
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback']))
						{
							@trigger_error('Using the onversion_callback has been deprecated and will no longer work in Contao 5.0. Use the oncreate_version_callback instead.', E_USER_DEPRECATED);

							foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
							{
								if (is_array($callback))
								{
									$this->import($callback[0]);
									$this->{$callback[0]}->{$callback[1]}($this->strTable, $objModel->id, $this);
								}
								elseif (is_callable($callback))
								{
									$callback($this->strTable, $objModel->id, $this);
								}
							}
						}
					}

					// Set the current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
					if ($this->blnIsDbAssisted && $objModel !== null)
					{
						$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
									   ->execute(time(), $objModel->id);
					}
				}
			}

			// Submit buttons
			$arrButtons = array();
			$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['save'].'</button>';
			$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG']['MSC']['saveNclose'].'</button>';

			// Call the buttons_callback (see #4691)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
					}
					elseif (is_callable($callback))
					{
						$arrButtons = $callback($arrButtons, $this);
					}
				}
			}

			if (count($arrButtons) < 3)
			{
				$strButtons = implode(' ', $arrButtons);
			}
			else
			{
				$strButtons = array_shift($arrButtons) . ' ';
				$strButtons .= '<div class="split-button">';
				$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<li>' . $strButton . '</li>';
				}

				$strButtons .= '</ul></div>';
			}

			// Add the form
			$return = '

<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post">
<div class="tl_formbody_edit nogrid">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . $strButtons . '
</div>

</div>
</form>';

			// Set the focus if there is an error
			if ($this->noReload)
			{
				$return .= '

<script>
  window.addEvent(\'domready\', function() {
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
  });
</script>';
			}

			// Reload the page to prevent _POST variables from being sent twice
			if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
			{
				if (isset($_POST['saveNclose']))
				{
					\System::setCookie('BE_PAGE_OFFSET', 0, 0);
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		}

		// Else show a form to select the fields
		else
		{
			$options = '';
			$fields = array();

			// Add fields of the current table
			$fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

			// Show all non-excluded fields
			foreach ($fields as $field)
			{
				if (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType']) || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'])))
				{
					$options .= '
  <input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.\StringUtil::specialchars($field).'"> <label for="all_'.$field.'" class="tl_checkbox_label">'.(($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?: ($GLOBALS['TL_LANG']['MSC'][$field][0] ?: $field)).' <span style="color:#999;padding-left:3px">['.$field.']</span>').'</label><br>';
				}
			}

			$blnIsError = ($_POST && empty($_POST['all_fields']));

			// Return the select menu
			$return .= '

<form action="'.ampersand(\Environment::get('request'), true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($blnIsError ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<div class="tl_tbox">
<div class="widget">
<fieldset class="tl_checkbox_container">
  <legend'.($blnIsError ? ' class="error"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>'.$options.'
</fieldset>'.($blnIsError ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : ((\Config::get('showHelp') && strlen($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
<p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][1].'</p>' : '')).'
</div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['continue'].'</button>
</div>

</div>
</form>';
		}

		// Return
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
	}


	/**
	 * Load the source editor
	 *
	 * @return string
	 *
	 * @throws InternalServerErrorException
	 */
	public function source()
	{
		$this->isValid($this->intId);

		if (is_dir(TL_ROOT .'/'. $this->intId))
		{
			throw new InternalServerErrorException('Folder "' . $this->intId . '" cannot be edited.');
		}
		elseif (!file_exists(TL_ROOT .'/'. $this->intId))
		{
			throw new InternalServerErrorException('File "' . $this->intId . '" does not exist.');
		}

		$this->import('BackendUser', 'User');

		// Check user permission
		if (!$this->User->hasAccess('f5', 'fop'))
		{
			throw new AccessDeniedException('Not enough permissions to edit the file source of file "' . $this->intId . '".');
		}

		$objFile = new \File($this->intId);

		// Check whether file type is editable
		if (!in_array($objFile->extension, \StringUtil::trimsplit(',', strtolower(\Config::get('editableFiles')))))
		{
			throw new AccessDeniedException('File type "' . $objFile->extension . '" (' . $this->intId . ') is not allowed to be edited.');
		}

		$objMeta = null;
		$objVersions = null;

		// Add the versioning routines
		if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($this->intId))
		{
			$objMeta = \FilesModel::findByPath($objFile->value);

			if ($objMeta === null)
			{
				$objMeta = \Dbafs::addResource($objFile->value);
			}

			$objVersions = new \Versions($this->strTable, $objMeta->id);

			if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'])
			{
				// Compare versions
				if (\Input::get('versions'))
				{
					$objVersions->compare();
				}

				// Restore a version
				if (\Input::post('FORM_SUBMIT') == 'tl_version' && \Input::post('version') != '')
				{
					$objVersions->restore(\Input::post('version'));

					// Purge the script cache (see #7005)
					if ($objFile->extension == 'css' || $objFile->extension == 'scss' || $objFile->extension == 'less')
					{
						$this->import('Automator');
						$this->Automator->purgeScriptCache();
					}

					$this->reload();
				}
			}

			$objVersions->initialize();
		}

		$strContent = $objFile->getContent();

		if ($objFile->extension == 'svgz')
		{
			$strContent = gzdecode($strContent);
		}

		// Process the request
		if (\Input::post('FORM_SUBMIT') == 'tl_files')
		{
			// Restore the basic entities (see #7170)
			$strSource = \StringUtil::restoreBasicEntities(\Input::postRaw('source'));

			// Save the file
			if (md5($strContent) != md5($strSource))
			{
				if ($objFile->extension == 'svgz')
				{
					$strSource = gzencode($strSource);
				}

				// Write the file
				$objFile->write($strSource);
				$objFile->close();

				// Update the database
				if ($this->blnIsDbAssisted && $objMeta !== null)
				{
					/** @var FilesModel $objMeta */
					$objMeta->hash = $objFile->hash;
					$objMeta->save();

					$objVersions->create();
				}

				// Purge the script cache (see #7005)
				if ($objFile->extension == 'css' || $objFile->extension == 'scss' || $objFile->extension == 'less')
				{
					$this->import('Automator');
					$this->Automator->purgeScriptCache();
				}
			}

			if (isset($_POST['saveNclose']))
			{
				\System::setCookie('BE_PAGE_OFFSET', 0, 0);
				$this->redirect($this->getReferer());
			}

			$this->reload();
		}

		$codeEditor = '';

		// Prepare the code editor
		if (\Config::get('useCE'))
		{
			/** @var BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_ace');
			$objTemplate->selector = 'ctrl_source';
			$objTemplate->type = $objFile->extension;

			$codeEditor = $objTemplate->parse();
		}

		// Versions overview
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] && $this->blnIsDbAssisted && $objVersions !== null)
		{
			$version = $objVersions->renderDropdown();
		}
		else
		{
			$version = '';
		}

		// Submit buttons
		$arrButtons = array();
		$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">'.$GLOBALS['TL_LANG']['MSC']['save'].'</button>';
		$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">'.$GLOBALS['TL_LANG']['MSC']['saveNclose'].'</button>';

		// Call the buttons_callback (see #4691)
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
				}
				elseif (is_callable($callback))
				{
					$arrButtons = $callback($arrButtons, $this);
				}
			}
		}

		if (count($arrButtons) < 3)
		{
			$strButtons = implode(' ', $arrButtons);
		}
		else
		{
			$strButtons = array_shift($arrButtons) . ' ';
			$strButtons .= '<div class="split-button">';
			$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . \Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

			foreach ($arrButtons as $strButton)
			{
				$strButtons .= '<li>' . $strButton . '</li>';
			}

			$strButtons .= '</ul></div>';
		}

		// Add the form
		return $version . '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_files" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_files">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<div class="tl_tbox">
  <div class="widget">
    <h3><label for="ctrl_source">'.$GLOBALS['TL_LANG']['tl_files']['editor'][0].'</label></h3>
    <textarea name="source" id="ctrl_source" class="tl_textarea monospace" rows="12" cols="80" style="height:400px" onfocus="Backend.getScrollOffset()">' . "\n" . htmlspecialchars($strContent) . '</textarea>' . ((\Config::get('showHelp') && strlen($GLOBALS['TL_LANG']['tl_files']['editor'][1])) ? '
    <p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['tl_files']['editor'][1].'</p>' : '') . '
  </div>
</div>
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . $strButtons . '
</div>

</div>
</form>' . "\n\n" . $codeEditor;
	}


	/**
	 * Protect a folder
	 *
	 * @throws InternalServerErrorException
	 */
	public function protect()
	{
		if (!is_dir(TL_ROOT . '/' . $this->intId))
		{
			throw new InternalServerErrorException('Resource "' . $this->intId . '" is not a directory.');
		}

		// Protect or unprotect the folder
		if (file_exists(TL_ROOT . '/' . $this->intId . '/.public'))
		{
			$objFolder = new \Folder($this->intId);
			$objFolder->protect();

			$this->import('Automator');
			$this->Automator->generateSymlinks();

			$this->log('Folder "'.$this->intId.'" has been protected', __METHOD__, TL_FILES);
		}
		else
		{
			$objFolder = new \Folder($this->intId);
			$objFolder->unprotect();

			$this->import('Automator');
			$this->Automator->generateSymlinks();

			$this->log('The protection from folder "'.$this->intId.'" has been removed', __METHOD__, TL_FILES);
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Save the current value
	 *
	 * @param mixed $varValue
	 *
	 * @throws \Exception
	 */
	protected function save($varValue)
	{
		if (\Input::post('FORM_SUBMIT') != $this->strTable)
		{
			return;
		}

		$arrData = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField];

		// File names
		if ($this->strField == 'name')
		{
			if (!file_exists(TL_ROOT . '/' . $this->strPath . '/' . $this->varValue . $this->strExtension) || !$this->isMounted($this->strPath . '/' . $this->varValue . $this->strExtension) || $this->varValue === $varValue)
			{
				return;
			}

			$this->import('Files');
			$varValue = Utf8::toAscii($varValue);

			// Trigger the save_callback
			if (is_array($arrData['save_callback']))
			{
				foreach ($arrData['save_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
					}
					elseif (is_callable($callback))
					{
						$varValue = $callback($varValue, $this);
					}
				}
			}

			// The target exists
			if (strcasecmp($this->strPath . '/' . $this->varValue . $this->strExtension, $this->strPath . '/' . $varValue . $this->strExtension) !== 0 && file_exists(TL_ROOT . '/' . $this->strPath . '/' . $varValue . $this->strExtension))
			{
				throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['fileExists'], $varValue));
			}

			$arrImageTypes = \StringUtil::trimsplit(',', strtolower(\Config::get('validImageTypes')));

			// Remove potentially existing thumbnails (see #6641)
			if (in_array(substr($this->strExtension, 1), $arrImageTypes))
			{
				foreach (glob(\System::getContainer()->getParameter('contao.image.target_dir') . '/*/' . $this->varValue . '-*' . $this->strExtension) as $strThumbnail)
				{
					$this->Files->delete(\StringUtil::stripRootDir($strThumbnail));
				}
			}

			// Rename the file
			$this->Files->rename($this->strPath . '/' . $this->varValue . $this->strExtension, $this->strPath . '/' . $varValue . $this->strExtension);

			// New folders
			if (stristr($this->intId, '__new__') !== false)
			{
				// Update the database
				if ($this->blnIsDbAssisted && \Dbafs::shouldBeSynchronized($this->strPath . '/' . $varValue . $this->strExtension))
				{
					$this->objActiveRecord = \Dbafs::addResource($this->strPath . '/' . $varValue . $this->strExtension);
				}

				$this->log('Folder "'.$this->strPath.'/'.$varValue.$this->strExtension.'" has been created', __METHOD__, TL_FILES);
			}
			else
			{
				// Update the database
				if ($this->blnIsDbAssisted)
				{
					$syncSource = \Dbafs::shouldBeSynchronized($this->strPath . '/' . $this->varValue . $this->strExtension);
					$syncTarget = \Dbafs::shouldBeSynchronized($this->strPath . '/' . $varValue . $this->strExtension);

					if ($syncSource && $syncTarget)
					{
						\Dbafs::moveResource($this->strPath . '/' . $this->varValue . $this->strExtension, $this->strPath . '/' . $varValue . $this->strExtension);
					}
					elseif ($syncSource)
					{
						\Dbafs::deleteResource($this->strPath . '/' . $this->varValue . $this->strExtension);
					}
					elseif ($syncTarget)
					{
						\Dbafs::addResource($this->strPath . '/' . $varValue . $this->strExtension);
					}

					$this->blnCreateNewVersion = true;
				}

				$this->log('File or folder "'.$this->strPath.'/'.$this->varValue.$this->strExtension.'" has been renamed to "'.$this->strPath.'/'.$varValue.$this->strExtension.'"', __METHOD__, TL_FILES);
			}

			$strWebDir = \StringUtil::stripRootDir(\System::getContainer()->getParameter('contao.web_dir'));

			// Update the symlinks
			if (is_link(TL_ROOT . '/' . $strWebDir . '/' . $this->strPath . '/' . $this->varValue . $this->strExtension))
			{
				$this->Files->delete($strWebDir . '/' . $this->strPath . '/' . $this->varValue . $this->strExtension);
				SymlinkUtil::symlink($this->strPath . '/' . $varValue . $this->strExtension, $strWebDir . '/' . $this->strPath . '/' . $varValue . $this->strExtension, TL_ROOT);
			}

			// Set the new value so the input field can show it
			if (\Input::get('act') == 'editAll')
			{
				/** @var SessionInterface $objSession */
				$objSession = \System::getContainer()->get('session');

				$session = $objSession->all();

				if (($index = array_search($this->strPath.'/'.$this->varValue.$this->strExtension, $session['CURRENT']['IDS'])) !== false)
				{
					$session['CURRENT']['IDS'][$index] = $this->strPath.'/'.$varValue.$this->strExtension;
					$objSession->replace($session);
				}
			}

			$this->varValue = $varValue;
		}
		elseif ($this->blnIsDbAssisted && $this->objActiveRecord !== null)
		{
			// Convert date formats into timestamps
			if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
			{
				$objDate = new \Date($varValue, \Date::getFormatFromRgxp($arrData['eval']['rgxp']));
				$varValue = $objDate->tstamp;
			}

			// Make sure unique fields are unique
			if ($arrData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue($this->strTable, $this->strField, $varValue, $this->objActiveRecord->id))
			{
				throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $this->strField));
			}

			// Handle multi-select fields in "override all" mode
			if (\Input::get('act') == 'overrideAll' && ($arrData['inputType'] == 'checkbox' || $arrData['inputType'] == 'checkboxWizard') && $arrData['eval']['multiple'])
			{
				if ($this->objActiveRecord !== null)
				{
					$new = \StringUtil::deserialize($varValue, true);
					$old = \StringUtil::deserialize($this->objActiveRecord->{$this->strField}, true);

					switch (\Input::post($this->strInputName . '_update'))
					{
						case 'add':
							$varValue = array_values(array_unique(array_merge($old, $new)));
							break;

						case 'remove':
							$varValue = array_values(array_diff($old, $new));
							break;

						case 'replace':
							$varValue = $new;
							break;
					}

					if (!is_array($varValue) || empty($varValue))
					{
						$varValue = '';
					}
					elseif (isset($arrData['eval']['csv']))
					{
						$varValue = implode($arrData['eval']['csv'], $varValue); // see #2890
					}
					else
					{
						$varValue = serialize($varValue);
					}
				}
			}

			// Convert arrays (see #2890)
			if ($arrData['eval']['multiple'] && isset($arrData['eval']['csv']))
			{
				$varValue = implode($arrData['eval']['csv'], \StringUtil::deserialize($varValue, true));
			}

			// Trigger the save_callback
			if (is_array($arrData['save_callback']))
			{
				foreach ($arrData['save_callback'] as $callback)
				{
					if (is_array($callback))
					{
						$this->import($callback[0]);
						$varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
					}
					elseif (is_callable($callback))
					{
						$varValue = $callback($varValue, $this);
					}
				}
			}

			// Save the value if there was no error
			if (($varValue != '' || !$arrData['eval']['doNotSaveEmpty']) && ($this->varValue != $varValue || $arrData['eval']['alwaysSave']))
			{
				// If the field is a fallback field, empty all other columns
				if ($arrData['eval']['fallback'] && $varValue != '')
				{
					$this->Database->execute("UPDATE " . $this->strTable . " SET " . $this->strField . "=''");
				}

				// Set the correct empty value (see #6284, #6373)
				if ($varValue === '')
				{
					$varValue = \Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['sql']);
				}

				$this->objActiveRecord->{$this->strField} = $varValue;
				$this->objActiveRecord->save();

				$this->blnCreateNewVersion = true;
				$this->varValue = \StringUtil::deserialize($varValue);
			}
		}
	}


	/**
	 * Synchronize the file system with the database
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 */
	public function sync()
	{
		if (!$this->blnIsDbAssisted)
		{
			return '';
		}

		$this->import('BackendUser', 'User');
		$this->loadLanguageFile('tl_files');

		// Check the permission to synchronize
		if (!$this->User->hasAccess('f6', 'fop'))
		{
			throw new AccessDeniedException('Not enough permissions to synchronize the file system.');
		}

		// Synchronize
		$strLog = \Dbafs::syncFiles();

		// Show the results
		$arrMessages = array();
		$arrCounts   = array('Added'=>0, 'Changed'=>0, 'Unchanged'=>0, 'Moved'=>0, 'Deleted'=>0);

		// Read the log file
		$fh = fopen(TL_ROOT . '/' . $strLog, 'rb');

		while (($buffer = fgets($fh)) !== false)
		{
			list($type, $file) = explode('] ', trim(substr($buffer, 1)), 2);

			// Add a message depending on the type
			switch ($type)
			{
				case 'Added';
					$arrMessages[] = '<p class="tl_new">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncAdded'], \StringUtil::specialchars($file)) . '</p>';
					break;

				case 'Changed';
					$arrMessages[] = '<p class="tl_info">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncChanged'], \StringUtil::specialchars($file)) . '</p>';
					break;

				case 'Unchanged';
					$arrMessages[] = '<p class="tl_confirm hidden">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncUnchanged'], \StringUtil::specialchars($file)) . '</p>';
					break;

				case 'Moved';
					list($source, $target) = explode(' to ', $file, 2);
					$arrMessages[] = '<p class="tl_info">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncMoved'], \StringUtil::specialchars($source), \StringUtil::specialchars($target)) . '</p>';
					break;

				case 'Deleted';
					$arrMessages[] = '<p class="tl_error">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncDeleted'], \StringUtil::specialchars($file)) . '</p>';
					break;
			}

			++$arrCounts[$type];
		}

		// Close the log file
		unset($buffer);
		fclose($fh);

		// Confirm
		\Message::addConfirmation($GLOBALS['TL_LANG']['tl_files']['syncComplete']);

		$return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>
'.\Message::generate().'
<div id="sync-results">
  <p class="left">' . sprintf($GLOBALS['TL_LANG']['tl_files']['syncResult'], \System::getFormattedNumber($arrCounts['Added'], 0), \System::getFormattedNumber($arrCounts['Changed'], 0), \System::getFormattedNumber($arrCounts['Unchanged'], 0), \System::getFormattedNumber($arrCounts['Moved'], 0), \System::getFormattedNumber($arrCounts['Deleted'], 0)) . '</p>
  <p class="right"><input type="checkbox" id="show-hidden" class="tl_checkbox" onclick="Backend.toggleUnchanged()"> <label for="show-hidden">' . $GLOBALS['TL_LANG']['tl_files']['syncShowUnchanged'] . '</label></p>
</div>
<div class="tl_message" id="result-list">';

		// Add the messages
		foreach ($arrMessages as $strMessage)
		{
			$return .= "\n  " . $strMessage;
		}

		$return .= '
</div>

<div class="tl_submit_container">
  <a href="'.$this->getReferer(true).'" class="tl_submit" style="display:inline-block">'.$GLOBALS['TL_LANG']['MSC']['continue'].'</a>
</div>
';

		return $return;
	}


	/**
	 * Return the name of the current palette
	 *
	 * @return string
	 */
	public function getPalette()
	{
		return $GLOBALS['TL_DCA'][$this->strTable]['palettes']['default'];
	}


	/**
	 * Generate a particular subpart of the tree and return it as HTML string
	 *
	 * @param string  $strFolder
	 * @param integer $level
	 *
	 * @return string
	 */
	public function ajaxTreeView($strFolder, $level)
	{
		if (!\Environment::get('isAjaxRequest'))
		{
			return '';
		}

		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');

		// Check clipboard
		if (!empty($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$blnProtected = true;
		$strPath = $strFolder;

		// Check for public parent folders (see #213)
		while ($strPath != '' && $strPath != '.')
		{
			if (file_exists(TL_ROOT . '/' . $strPath . '/.public'))
			{
				$blnProtected = false;
				break;
			}

			$strPath = dirname($strPath);
		}

		$this->import('Files');
		$this->import('BackendUser', 'User');

		return $this->generateTree(TL_ROOT.'/'.$strFolder, ($level * 20), false, $blnProtected, ($blnClipboard ? $arrClipboard : false));
	}


	/**
	 * Render the file tree and return it as HTML string
	 *
	 * @param string  $path
	 * @param integer $intMargin
	 * @param boolean $mount
	 * @param boolean $blnProtected
	 * @param array   $arrClipboard
	 * @param array   $arrFound
	 *
	 * @return string
	 */
	protected function generateTree($path, $intMargin, $mount=false, $blnProtected=true, $arrClipboard=null, $arrFound=array())
	{
		static $session;

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

		$session = $objSessionBag->all();

		// Get the session data and toggle the nodes
		if (\Input::get('tg'))
		{
			$session['filetree'][\Input::get('tg')] = (isset($session['filetree'][\Input::get('tg')]) && $session['filetree'][\Input::get('tg')] == 1) ? 0 : 1;
			$objSessionBag->replace($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)tg=[^& ]*/i', '', \Environment::get('request')));
		}

		$return = '';
		$files = array();
		$folders = array();
		$intSpacing = 20;
		$level = ($intMargin / $intSpacing + 1);

		// Mount folder
		if ($mount)
		{
			$folders = array($path);
		}

		// Scan directory and sort the result
		else
		{
			foreach (scan($path) as $v)
			{
				if (strncmp($v, '.', 1) === 0)
				{
					continue;
				}

				if (is_file($path . '/' . $v))
				{
					$files[] = $path . '/' . $v;
				}
				else
				{
					if ($v == '__new__')
					{
						$this->Files->rmdir(\StringUtil::stripRootDir($path) . '/' . $v);
					}
					else
					{
						$folders[] = $path . '/' . $v;
					}
				}
			}

			natcasesort($folders);
			$folders = array_values($folders);

			natcasesort($files);
			$files = array_values($files);
		}

		// Folders
		for ($f=0, $c=count($folders); $f<$c; $f++)
		{
			$md5 = substr(md5($folders[$f]), 0, 8);
			$content = scan($folders[$f]);
			$currentFolder = \StringUtil::stripRootDir($folders[$f]);
			$session['filetree'][$md5] = is_numeric($session['filetree'][$md5]) ? $session['filetree'][$md5] : 0;
			$currentEncoded = $this->urlEncode($currentFolder);
			$countFiles = count($content);

			// Subtract files that will not be shown
			foreach ($content as $file)
			{
				if (strncmp($file, '.', 1) === 0)
				{
					--$countFiles;
				}
				elseif (!empty($arrFound) && !in_array($currentFolder . '/' . $file, $arrFound) && !preg_grep('/^' . preg_quote($currentFolder . '/' . $file, '/') . '\//', $arrFound))
				{
					--$countFiles;
				}
			}

			if (!empty($arrFound) && $countFiles < 1 && !in_array($currentFolder, $arrFound))
			{
				continue;
			}

			$blnIsOpen = (!empty($arrFound) || $session['filetree'][$md5] == 1);

			// Always show selected nodes
			if (!$blnIsOpen && !empty($this->arrPickerValue) && count(preg_grep('/^' . preg_quote($currentFolder, '/') . '\//', $this->arrPickerValue)))
			{
				$blnIsOpen = true;
			}

			$return .= "\n  " . '<li class="tl_folder click2edit toggle_select hover-div"><div class="tl_left" style="padding-left:'.($intMargin + (($countFiles < 1) ? 20 : 0)).'px">';

			// Add a toggle button if there are childs
			if ($countFiles > 0)
			{
				$img = $blnIsOpen ? 'folMinus.svg' : 'folPlus.svg';
				$alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
				$return .= '<a href="'.$this->addToUrl('tg='.$md5).'" title="'.\StringUtil::specialchars($alt).'" onclick="Backend.getScrollOffset(); return AjaxRequest.toggleFileManager(this, \'filetree_'.$md5.'\', \''.$currentFolder.'\', '.$level.')">'.\Image::getHtml($img, '', 'style="margin-right:2px"').'</a>';
			}

			$protected = $blnProtected;

			// Check whether the folder is public
			if ($protected === true && array_search('.public', $content) !== false)
			{
				$protected = false;
			}

			$folderImg = $protected ? 'folderCP.svg' : 'folderC.svg';

			// Add the current folder
			$strFolderNameEncoded = \StringUtil::convertEncoding(\StringUtil::specialchars(basename($currentFolder)), \Config::get('characterSet'));
			$return .= \Image::getHtml($folderImg, '').' <a href="' . $this->addToUrl('fn='.$currentEncoded) . '" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']).'"><strong>'.$strFolderNameEncoded.'</strong></a></div> <div class="tl_right">';

			// Paste buttons
			if ($arrClipboard !== false && \Input::get('act') != 'select')
			{
				$imagePasteInto = \Image::getHtml('pasteinto.svg', $GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]);
				$return .= (($arrClipboard['mode'] == 'cut' || $arrClipboard['mode'] == 'copy') && preg_match('/^' . preg_quote($arrClipboard['id'], '/') . '/i', $currentFolder)) ? \Image::getHtml('pasteinto_.svg') : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$currentEncoded.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1]).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ';
			}
			// Default buttons
			else
			{
				// Do not display buttons for mounted folders
				if ($this->User->isAdmin || !in_array($currentFolder, $this->User->filemounts))
				{
					$return .= (\Input::get('act') == 'select') ? '<input type="checkbox" name="IDS[]" id="ids_'.md5($currentEncoded).'" class="tl_tree_checkbox" value="'.$currentEncoded.'">' : $this->generateButtons(array('id'=>$currentEncoded, 'popupWidth'=>600, 'popupHeight'=>160, 'fileNameEncoded'=>$strFolderNameEncoded), $this->strTable);
				}

				// Upload button
				if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] && \Input::get('act') != 'select')
				{
					$return .= ' <a href="'.$this->addToUrl('&amp;act=move&amp;mode=2&amp;pid='.$currentEncoded).'" title="'.\StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['tl_files']['uploadFF'], $currentEncoded)).'">'.\Image::getHtml('new.svg', $GLOBALS['TL_LANG'][$this->strTable]['move'][0]).'</a>';
				}

				if ($this->strPickerField)
				{
					$return .= $this->getPickerInputField($currentEncoded, $GLOBALS['TL_DCA'][$this->strPickerTable]['fields'][$this->strPickerField]['eval']['filesOnly'] ? ' disabled' : '');
				}
			}

			$return .= '</div><div style="clear:both"></div></li>';

			// Call the next node
			if (!empty($content) && $blnIsOpen)
			{
				$return .= '<li class="parent" id="filetree_'.$md5.'"><ul class="level_'.$level.'">';
				$return .= $this->generateTree($folders[$f], ($intMargin + $intSpacing), false, $protected, $arrClipboard, $arrFound);
				$return .= '</ul></li>';
			}
		}

		// Process files
		for ($h=0, $c=count($files); $h<$c; $h++)
		{
			$thumbnail = '';
			$popupWidth = 600;
			$popupHeight = 192;
			$currentFile = \StringUtil::stripRootDir($files[$h]);

			$objFile = new \File($currentFile);

			if (!empty($this->arrValidFileTypes) && !in_array($objFile->extension, $this->arrValidFileTypes))
			{
				continue;
			}

			// Ignore files not matching the search criteria
			if (!empty($arrFound) && !in_array($currentFile, $arrFound))
			{
				continue;
			}

			$currentEncoded = $this->urlEncode($currentFile);
			$return .= "\n  " . '<li class="tl_file click2edit toggle_select hover-div"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px">';
			$thumbnail .= ' <span class="tl_gray">('.$this->getReadableSize($objFile->filesize);

			if ($objFile->width && $objFile->height)
			{
				$thumbnail .= ', '.$objFile->width.'x'.$objFile->height.' px';
			}

			$thumbnail .= ')</span>';

			// Generate the thumbnail
			if ($objFile->isImage)
			{
				if ($objFile->viewHeight > 0)
				{
					if ($objFile->width && $objFile->height)
					{
						$popupWidth = ($objFile->width > 600) ? ($objFile->width + 61) : 661;
						$popupHeight = ($objFile->height + 236);
					}
					else
					{
						$popupWidth = 661;
						$popupHeight = 625 / $objFile->viewWidth * $objFile->viewHeight + 236;
					}

					if (\Config::get('thumbnails') && ($objFile->isSvgImage || $objFile->height <= \Config::get('gdMaxImgHeight') && $objFile->width <= \Config::get('gdMaxImgWidth')))
					{
						$thumbnail .= '<br>' . \Image::getHtml(\System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . rawurldecode($currentEncoded), array(400, 50, 'box'))->getUrl(TL_ROOT), '', 'style="margin:0 0 2px -19px"');
						$importantPart = \System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . rawurldecode($currentEncoded))->getImportantPart();

						if ($importantPart->getPosition()->getX() > 0 || $importantPart->getPosition()->getY() > 0 || $importantPart->getSize()->getWidth() < $objFile->width || $importantPart->getSize()->getHeight() < $objFile->height)
						{
							$thumbnail .= ' ' . \Image::getHtml(\System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . rawurldecode($currentEncoded), (new ResizeConfiguration())->setWidth(320)->setHeight(40)->setMode(ResizeConfiguration::MODE_BOX)->setZoomLevel(100))->getUrl(TL_ROOT), '', 'style="margin:0 0 2px 0;vertical-align:bottom"');
						}
					}
				}
				else
				{
					$popupHeight = 386; // dimensionless SVGs are rendered at 300x150px, so the popup needs to be 150px + 236px high
				}
			}

			$strFileNameEncoded = \StringUtil::convertEncoding(\StringUtil::specialchars(basename($currentFile)), \Config::get('characterSet'));

			// No popup links for templates and in the popup file manager
			if ($this->strTable == 'tl_templates' || \Input::get('popup'))
			{
				$return .= \Image::getHtml($objFile->icon).' '.$strFileNameEncoded.$thumbnail.'</div> <div class="tl_right">';
			}
			else
			{
				$return .= '<a href="'. $currentEncoded.'" title="'.\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['view']).'" target="_blank">' . \Image::getHtml($objFile->icon, $objFile->mime).'</a> '.$strFileNameEncoded.$thumbnail.'</div> <div class="tl_right">';
			}

			// Buttons
			if ($arrClipboard !== false && \Input::get('act') != 'select')
			{
				$_buttons = '&nbsp;';
			}
			else
			{
				$_buttons = (\Input::get('act') == 'select') ? '<input type="checkbox" name="IDS[]" id="ids_'.md5($currentEncoded).'" class="tl_tree_checkbox" value="'.$currentEncoded.'">' : $this->generateButtons(array('id'=>$currentEncoded, 'popupWidth'=>$popupWidth, 'popupHeight'=>$popupHeight, 'fileNameEncoded'=>$strFileNameEncoded), $this->strTable);

				if ($this->strPickerField)
				{
					$_buttons .= $this->getPickerInputField($currentEncoded, ($GLOBALS['TL_DCA'][$this->strPickerTable]['fields'][$this->strPickerField]['eval']['files'] || $GLOBALS['TL_DCA'][$this->strPickerTable]['fields'][$this->strPickerField]['eval']['filesOnly']) ? '' : ' disabled');
				}
			}

			$return .= $_buttons . '</div><div style="clear:both"></div></li>';
		}

		return $return;
	}


	/**
	 * Build the sort panel and return it as string
	 *
	 * @return string
	 */
	protected function panel()
	{
		// Reset all filters
		if (isset($_POST['filter_reset']) && \Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			/** @var AttributeBagInterface $objSessionBag */
			$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

			$data = $objSessionBag->all();

			unset($data['search'][$this->strTable]);

			$objSessionBag->replace($data);

			$this->reload();
		}

		$search = $this->searchMenu();

		if (\Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			$this->reload();
		}

		return '
<form action="'.ampersand(\Environment::get('request'), true).'" class="tl_form" method="post">
<div class="tl_formbody">
  <input type="hidden" name="FORM_SUBMIT" value="tl_filters">
  <input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
  <div class="tl_panel cf">
    <div class="tl_submit_panel tl_subpanel">
      <button name="filter" id="filter" class="tl_img_submit filter_apply" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['applyTitle']) . '">' . $GLOBALS['TL_LANG']['MSC']['apply'] . '</button>
      <button name="filter_reset" id="filter_reset" value="1" class="tl_img_submit filter_reset" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['resetTitle']) . '">' . $GLOBALS['TL_LANG']['MSC']['reset'] . '</button>
    </div>'.$search.'
  </div>
</div>
</form>
';
	}


	/**
	 * Return a search form that allows to search results using regular expressions
	 *
	 * @return string
	 */
	protected function searchMenu()
	{
		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \System::getContainer()->get('session')->getBag('contao_backend');

		$session = $objSessionBag->all();

		// Store search value in the current session
		if (\Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			$strField = \Input::post('tl_field', true);
			$strKeyword = ltrim(\Input::postRaw('tl_value'), '*');

			// Make sure the regular expression is valid
			if ($strKeyword != '')
			{
				try
				{
					$this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . $strField . " REGEXP ?")
								   ->limit(1)
								   ->execute($strKeyword);
				}
				catch (\Exception $e)
				{
					$strKeyword = '';
				}
			}

			$session['search'][$this->strTable]['field'] = $strField;
			$session['search'][$this->strTable]['value'] = $strKeyword;

			$objSessionBag->replace($session);
		}

		// Set the search value from the session
		elseif ($session['search'][$this->strTable]['value'] != '')
		{
			$strPattern = "CAST(name AS CHAR) REGEXP ?";

			if (substr(\Config::get('dbCollation'), -3) == '_ci')
			{
				$strPattern = "LOWER(CAST(name AS CHAR)) REGEXP LOWER(?)";
			}

			if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields']['name']['foreignKey']))
			{
				list($t, $f) = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields']['name']['foreignKey']);
				$this->procedure[] = "(" . $strPattern . " OR " . sprintf($strPattern, "(SELECT $f FROM $t WHERE $t.id={$this->strTable}.name)") . ")";
				$this->values[] = $session['search'][$this->strTable]['value'];
			}
			else
			{
				$this->procedure[] = $strPattern;
			}

			$this->values[] = $session['search'][$this->strTable]['value'];
		}

		$active = ($session['search'][$this->strTable]['value'] != '') ? true : false;

		return '
    <div class="tl_search tl_subpanel">
      <strong>' . $GLOBALS['TL_LANG']['MSC']['search'] . ':</strong>
      <select name="tl_field" class="tl_select' . ($active ? ' active' : '') . '">
        <option value="name">'.($GLOBALS['TL_DCA'][$this->strTable]['fields']['name']['label'][0] ?: (is_array($GLOBALS['TL_LANG']['MSC']['name']) ? $GLOBALS['TL_LANG']['MSC']['name'][0] : $GLOBALS['TL_LANG']['MSC']['name'])).'</option>
      </select>
      <span>=</span>
      <input type="search" name="tl_value" class="tl_text' . ($active ? ' active' : '') . '" value="'.\StringUtil::specialchars($session['search'][$this->strTable]['value']).'">
    </div>';
	}


	/**
	 * Return true if the current folder is mounted
	 *
	 * @param string $strFolder
	 *
	 * @return boolean
	 */
	protected function isMounted($strFolder)
	{
		if ($strFolder == '')
		{
			return false;
		}

		if (empty($this->arrFilemounts))
		{
			return true;
		}

		$path = $strFolder;

		while (is_array($this->arrFilemounts) && substr_count($path, '/') > 0)
		{
			if (in_array($path, $this->arrFilemounts))
			{
				return true;
			}

			$path = dirname($path);
		}

		return false;
	}


	/**
	 * Check a file operation
	 *
	 * @param string $strFile
	 *
	 * @return boolean
	 *
	 * @throws AccessDeniedException
	 */
	protected function isValid($strFile)
	{
		$strFolder = \Input::get('pid', true);

		// Check the path
		if (\Validator::isInsecurePath($strFile))
		{
			throw new AccessDeniedException('Invalid file name "' . $strFile . '" (hacking attempt).');
		}
		elseif (\Validator::isInsecurePath($strFolder))
		{
			throw new AccessDeniedException('Invalid folder name "' . $strFolder . '" (hacking attempt).');
		}

		// Check for valid file types
		if (!empty($this->arrValidFileTypes) && is_file(TL_ROOT . '/' . $strFile))
		{
			$fileinfo = preg_replace('/.*\.(.*)$/ui', '$1', $strFile);

			if (!in_array(strtolower($fileinfo), $this->arrValidFileTypes))
			{
				throw new AccessDeniedException('File "' . $strFile . '" is not an allowed file type.');
			}
		}

		// Check whether the file is within the files directory
		if (!preg_match('/^'.preg_quote(\Config::get('uploadPath'), '/').'/i', $strFile))
		{
			throw new AccessDeniedException('File or folder "' . $strFile . '" is not within the files directory.');
		}

		// Check whether the parent folder is within the files directory
		if ($strFolder && !preg_match('/^'.preg_quote(\Config::get('uploadPath'), '/').'/i', $strFolder))
		{
			throw new AccessDeniedException('Parent folder "' . $strFolder . '" is not within the files directory.');
		}

		// Do not allow file operations on root folders
		if (\Input::get('act') == 'edit' || \Input::get('act') == 'paste' || \Input::get('act') == 'delete')
		{
			$this->import('BackendUser', 'User');

			if (!$this->User->isAdmin && in_array($strFile, $this->User->filemounts))
			{
				throw new AccessDeniedException('Attempt to edit, copy, move or delete the root folder "' . $strFile . '".');
			}
		}

		return true;
	}


	/**
	 * Return an array of encrypted folder names
	 *
	 * @param string $strPath
	 *
	 * @return array
	 */
	protected function getMD5Folders($strPath)
	{
		$arrFiles = array();

		foreach (scan(TL_ROOT . '/' . $strPath) as $strFile)
		{
			if (!is_dir(TL_ROOT . '/' . $strPath . '/' . $strFile))
			{
				continue;
			}

			$arrFiles[substr(md5(TL_ROOT . '/' . $strPath . '/' . $strFile), 0, 8)] = 1;

			// Do not use array_merge() here (see #8105)
			foreach ($this->getMD5Folders($strPath . '/' . $strFile) as $k=>$v)
			{
				$arrFiles[$k] = $v;
			}
		}

		return $arrFiles;
	}


	/**
	 * Check if a path is protected (see #287)
	 *
	 * @param string $path
	 *
	 * @return boolean
	 */
	protected function isProtectedPath($path)
	{
		do
		{
			if (file_exists(TL_ROOT . '/' . $path . '/.public'))
			{
				return false;
			}

			$path = dirname($path);
		}
		while ($path != '.');

		return true;
	}


	/**
	 * Set the picker value
	 */
	protected function setPickerValue()
	{
		$varValue = \Input::get('value');

		if (empty($varValue))
		{
			return;
		}

		$varValue = array_filter(explode(',', $varValue));

		if (empty($varValue))
		{
			return;
		}

		$this->arrPickerValue = $varValue;

		// TinyMCE will pass the path instead of the ID
		if (strpos($varValue[0], \Config::get('uploadPath') . '/') === 0)
		{
			return;
		}

		// Ignore the numeric IDs when in switch mode (TinyMCE)
		if (\Input::get('switch'))
		{
			return;
		}

		$objFiles = \FilesModel::findMultipleByIds($varValue);

		if ($objFiles !== null)
		{
			$this->arrPickerValue = array_values($objFiles->fetchEach('path'));
		}
	}
}
