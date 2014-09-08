<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_user
 */
$GLOBALS['TL_DCA']['tl_user'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'onload_callback' =>
		[
			['tl_user', 'checkPermission']
		],
		'onsubmit_callback' =>
		[
			['tl_user', 'storeDateAdded'],
			['tl_user', 'checkRemoveSession']
		],
		'ondelete_callback' =>
		[
			['tl_user', 'removeSession']
		],
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary',
				'username' => 'unique',
				'email' => 'index'
			]
		]
	],

	// List
	'list' =>
	[
		'sorting' =>
		[
			'mode'                    => 2,
			'fields'                  => ['dateAdded DESC'],
			'flag'                    => 1,
			'panelLayout'             => 'filter;sort,search,limit'
		],
		'label' =>
		[
			'fields'                  => ['icon', 'name', 'username', 'dateAdded'],
			'showColumns'             => true,
			'label_callback'          => ['tl_user', 'addIcon']
		],
		'global_operations' =>
		[
			'all' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			]
		],
		'operations' =>
		[
			'edit' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
				'button_callback'     => ['tl_user', 'editUser']
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => ['tl_user', 'copyUser']
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				'button_callback'     => ['tl_user', 'deleteUser']
			],
			'toggle' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => ['tl_user', 'toggleIcon']
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			],
			'su' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user']['su'],
				'href'                => 'key=su',
				'icon'                => 'su.gif',
				'button_callback'     => ['tl_user', 'switchUser']
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['inherit', 'admin'],
		'login'                       => '{name_legend},name,email;{backend_legend},language,uploader,showHelp,thumbnails,useRTE,useCE;{session_legend},session;{theme_legend:hide},backendTheme;{password_legend},password',
		'admin'                       => '{name_legend},username,name,email;{backend_legend:hide},language,uploader,showHelp,thumbnails,useRTE,useCE;{theme_legend:hide},backendTheme;{password_legend:hide},pwChange,password;{admin_legend},admin;{account_legend},disable,start,stop',
		'default'                     => '{name_legend},username,name,email;{backend_legend:hide},language,uploader,showHelp,thumbnails,useRTE,useCE;{theme_legend:hide},backendTheme;{password_legend:hide},pwChange,password;{admin_legend},admin;{groups_legend},groups,inherit;{account_legend},disable,start,stop',
		'group'                       => '{name_legend},username,name,email;{backend_legend:hide},language,uploader,showHelp,thumbnails,useRTE,useCE;{theme_legend:hide},backendTheme;{password_legend:hide},pwChange,password;{admin_legend},admin;{groups_legend},groups,inherit;{account_legend},disable,start,stop',
		'extend'                      => '{name_legend},username,name,email;{backend_legend:hide},language,uploader,showHelp,thumbnails,useRTE,useCE;{theme_legend:hide},backendTheme;{password_legend:hide},pwChange,password;{admin_legend},admin;{groups_legend},groups,inherit;{modules_legend},modules,themes;{pagemounts_legend},pagemounts,alpty;{filemounts_legend},filemounts,fop;{forms_legend},forms,formp;{account_legend},disable,start,stop',
		'custom'                      => '{name_legend},username,name,email;{backend_legend:hide},language,uploader,showHelp,thumbnails,useRTE,useCE;{theme_legend:hide},backendTheme;{password_legend:hide},pwChange,password;{admin_legend},admin;{groups_legend},groups,inherit;{modules_legend},modules,themes;{pagemounts_legend},pagemounts,alpty;{filemounts_legend},filemounts,fop;{forms_legend},forms,formp;{account_legend},disable,start,stop'
	],

	// Fields
	'fields' =>
	[
		'id' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		],
		'tstamp' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'username' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['username'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'extnd', 'nospace'=>true, 'unique'=>true, 'maxlength'=>64],
			'sql'                     => "varchar(64) COLLATE utf8_bin NULL"
		],
		'name' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'email' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'email', 'maxlength'=>255, 'unique'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'language' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['language'],
			'default'                 => str_replace('-', '_', $GLOBALS['TL_LANGUAGE']),
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => System::getLanguages(true),
			'eval'                    => ['rgxp'=>'locale', 'tl_class'=>'w50'],
			'sql'                     => "varchar(5) NOT NULL default ''"
		],
		'backendTheme' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['backendTheme'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => Backend::getThemes(),
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'uploader' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['uploader'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['DropZone', 'FileUpload'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_user'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'showHelp' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['showHelp'],
			'default'                 => 1,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'thumbnails' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['thumbnails'],
			'default'                 => 1,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'useRTE' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['useRTE'],
			'default'                 => 1,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'useCE' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['useCE'],
			'default'                 => 1,
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'password' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['password'],
			'exclude'                 => true,
			'inputType'               => 'password',
			'eval'                    => ['mandatory'=>true, 'preserveTags'=>true, 'minlength'=>Config::get('minPasswordLength')],
			'sql'                     => "varchar(128) NOT NULL default ''"
		],
		'pwChange' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['pwChange'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'admin' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['admin'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => ['submitOnChange'=>true],
			'save_callback' =>
			[
				['tl_user', 'checkAdminStatus']
			],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'groups' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['groups'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'foreignKey'              => 'tl_user_group.name',
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'belongsToMany', 'load'=>'lazy']
		],
		'inherit' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['inherit'],
			'exclude'                 => true,
			'default'                 => 'group',
			'inputType'               => 'radio',
			'options'                 => ['group', 'extend', 'custom'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_user'],
			'eval'                    => ['helpwizard'=>true, 'submitOnChange'=>true],
			'sql'                     => "varchar(12) NOT NULL default ''"
		],
		'modules' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['modules'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'options_callback'        => ['tl_user', 'getModules'],
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => ['multiple'=>true, 'helpwizard'=>true],
			'sql'                     => "blob NULL"
		],
		'themes' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['themes'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => ['css', 'modules', 'layout', 'theme_import', 'theme_export'],
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'pagemounts' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['pagemounts'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox'],
			'sql'                     => "blob NULL"
		],
		'alpty' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['alpty'],
			'default'                 => ['regular', 'redirect', 'forward'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => array_keys($GLOBALS['TL_PTY']),
			'reference'               => &$GLOBALS['TL_LANG']['PTY'],
			'eval'                    => ['multiple'=>true, 'helpwizard'=>true],
			'sql'                     => "blob NULL"
		],
		'filemounts' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['filemounts'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox'],
			'sql'                     => "blob NULL"
		],
		'fop' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['FOP']['fop'],
			'default'                 => ['f1', 'f2', 'f3'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => ['f1', 'f2', 'f3', 'f4', 'f5', 'f6'],
			'reference'               => &$GLOBALS['TL_LANG']['FOP'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'forms' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['forms'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_form.title',
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'formp' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['formp'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => ['create', 'delete'],
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'disable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['disable'],
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 2,
			'inputType'               => 'checkbox',
			'save_callback' =>
			[
				['tl_user', 'checkAdminDisable']
			],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'start' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"

		],
		'stop' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		],
		'session' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['session'],
			'exclude'                 => true,
			'input_field_callback'    => ['tl_user', 'sessionField'],
			'eval'                    => ['doNotShow'=>true],
			'sql'                     => "blob NULL"
		],
		'dateAdded' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
			'sorting'                 => true,
			'flag'                    => 6,
			'eval'                    => ['rgxp'=>'datim'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'lastLogin' =>
		[
			'eval'                    => ['rgxp'=>'datim', 'doNotShow'=>true],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'currentLogin' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['lastLogin'],
			'sorting'                 => true,
			'flag'                    => 6,
			'eval'                    => ['rgxp'=>'datim'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'loginCount' =>
		[
			'sql'                     => "smallint(5) unsigned NOT NULL default '3'"
		],
		'locked' =>
		[
			'eval'                    => ['rgxp'=>'datim'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		]
	]
];


/**
 * Class tl_user
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_user extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check permissions to edit table tl_user
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Check current action
		switch (Input::get('act'))
		{
			case 'create':
			case 'select':
			case 'show':
				// Allow
				break;

			case 'delete':
				if (Input::get('id') == $this->User->id)
				{
					$this->log('Attempt to delete own account ID "'.Input::get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				// no break;

			case 'edit':
			case 'copy':
			case 'toggle':
			default:
				$objUser = $this->Database->prepare("SELECT admin FROM tl_user WHERE id=?")
										  ->limit(1)
										  ->execute(Input::get('id'));

				if ($objUser->admin && Input::get('act') != '')
				{
					$this->log('Not enough permissions to '.Input::get('act').' administrator account ID "'.Input::get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
				$session = $this->Session->getData();
				$objUser = $this->Database->execute("SELECT id FROM tl_user WHERE admin=1");
				$session['CURRENT']['IDS'] = array_diff($session['CURRENT']['IDS'], $objUser->fetchEach('id'));
				$this->Session->setData($session);
				break;
		}
	}


	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @param Contao\DataContainer
	 * @param array
	 * @return string
	 */
	public function addIcon($row, $label, Contao\DataContainer $dc, $args)
	{
		$image = $row['admin'] ? 'admin' :  'user';

		if ($row['disable'] || strlen($row['start']) && $row['start'] > time() || strlen($row['stop']) && $row['stop'] < time())
		{
			$image .= '_';
		}

		$args[0] = sprintf('<div class="list_icon_new" style="background-image:url(\'%ssystem/themes/%s/images/%s.gif\')">&nbsp;</div>', TL_ASSETS_URL, Backend::getTheme(), $image);
		return $args;
	}


	/**
	 * Return the edit user button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function editUser($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || !$row['admin']) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the copy page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function copyUser($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($GLOBALS['TL_DCA'][$table]['config']['closed'])
		{
			return '';
		}

		return ($this->User->isAdmin || !$row['admin']) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete page button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function deleteUser($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || !$row['admin']) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Generate a "switch account" button and return it as string
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function switchUser($row, $href, $label, $title, $icon)
	{
		if (!$this->User->isAdmin)
		{
			return '';
		}

		if (Input::get('key') == 'su' && Input::get('id'))
		{
			$this->Database->prepare("UPDATE tl_session SET pid=?, su=1 WHERE pid=?")
						   ->execute(Input::get('id'), $this->User->id);

			$this->redirect('contao/main.php');
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'">'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Return a checkbox to delete session data
	 * @param Contao\DataContainer
	 * @return string
	 */
	public function sessionField(Contao\DataContainer $dc)
	{
		if (Input::post('FORM_SUBMIT') == 'tl_user')
		{
			$arrPurge = Input::post('purge');

			if (is_array($arrPurge))
			{
				$this->import('Automator');

				if (in_array('purge_session', $arrPurge))
				{
					$this->Session->setData([]);
					Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['sessionPurged']);
				}

				if (in_array('purge_images', $arrPurge))
				{
					$this->Automator->purgeImageCache();
					Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['htmlPurged']);
				}

				if (in_array('purge_pages', $arrPurge))
				{
					$this->Automator->purgePageCache();
					Message::addConfirmation($GLOBALS['TL_LANG']['tl_user']['tempPurged']);
				}
			}
		}

		return '
<div>
  <fieldset class="tl_checkbox_container">
    <legend>'.$GLOBALS['TL_LANG']['tl_user']['session'][0].'</legend>
    <input type="checkbox" id="check_all_purge" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, \'ctrl_purge\')"> <label for="check_all_purge" style="color:#a6a6a6"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_0" class="tl_checkbox" value="purge_session" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_0">'.$GLOBALS['TL_LANG']['tl_user']['sessionLabel'].'</label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_1" class="tl_checkbox" value="purge_images" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_1">'.$GLOBALS['TL_LANG']['tl_user']['htmlLabel'].'</label><br>
    <input type="checkbox" name="purge[]" id="opt_purge_2" class="tl_checkbox" value="purge_pages" onfocus="Backend.getScrollOffset()"> <label for="opt_purge_2">'.$GLOBALS['TL_LANG']['tl_user']['tempLabel'].'</label>
  </fieldset>'.$dc->help().'
</div>';
	}


	/**
	 * Return all modules except profile modules
	 * @return array
	 */
	public function getModules()
	{
		$arrModules = [];

		foreach ($GLOBALS['BE_MOD'] as $k=>$v)
		{
			if (!empty($v))
			{
				unset($v['undo']);
				$arrModules[$k] = array_keys($v);
			}
		}

		return $arrModules;
	}


	/**
	 * Prevent administrators from downgrading their own account
	 * @param mixed
	 * @param Contao\DataContainer
	 * @return mixed
	 */
	public function checkAdminStatus($varValue, Contao\DataContainer $dc)
	{
		if ($varValue == '' && $this->User->id == $dc->id)
		{
			$varValue = 1;
		}

		return $varValue;
	}


	/**
	 * Prevent administrators from disabling their own account
	 * @param mixed
	 * @param Contao\DataContainer
	 * @return mixed
	 */
	public function checkAdminDisable($varValue, Contao\DataContainer $dc)
	{
		if ($varValue == 1 && $this->User->id == $dc->id)
		{
			$varValue = '';
		}

		return $varValue;
	}


	/**
	 * Store the date when the account has been added
	 * @param Contao\DataContainer
	 */
	public function storeDateAdded(Contao\DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord || $dc->activeRecord->dateAdded > 0)
		{
			return;
		}

		// Fallback solution for existing accounts
		if ($dc->activeRecord->lastLogin > 0)
		{
			$time = $dc->activeRecord->lastLogin;
		}
		else
		{
			$time = time();
		}

		$this->Database->prepare("UPDATE tl_user SET dateAdded=? WHERE id=?")
					   ->execute($time, $dc->id);
	}


	/**
	 * Check whether the user session should be removed
	 * @param Contao\DataContainer
	 */
	public function checkRemoveSession(Contao\DataContainer $dc)
	{
		if ($dc->activeRecord)
		{
			if ($dc->activeRecord->disable || ($dc->activeRecord->start != '' && $dc->activeRecord->start > time()) || ($dc->activeRecord->stop != '' && $dc->activeRecord->stop < time()))
			{
				$this->removeSession($dc);
			}
		}
	}


	/**
	 * Remove the session if a user is deleted (see #5353)
	 * @param Contao\DataContainer
	 */
	public function removeSession(Contao\DataContainer $dc)
	{
		if (!$dc->activeRecord)
		{
			return;
		}

		if ($dc->activeRecord->disable || Input::get('act') == 'delete' || Input::get('act') == 'deleteAll')
		{
			$this->Database->prepare("DELETE FROM tl_session WHERE name='BE_USER_AUTH' AND pid=?")
						   ->execute($dc->activeRecord->id);
		}
	}


	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_user::disable', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.$row['disable'];

		if ($row['disable'])
		{
			$icon = 'invisible.gif';
		}

		// Protect admin accounts
		if (!$this->User->isAdmin && $row['admin'])
		{
			return Image::getHtml($icon) . ' ';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Disable/enable a user group
	 * @param int
	 * @param bool
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check admin accounts
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');
		$this->checkPermission();

		// Protect own account
		if ($this->User->id == $intId)
		{
			return;
		}

		// Check permissions
		if (!$this->User->hasAccess('tl_user::disable', 'alexf'))
		{
			$this->log('Not enough permissions to activate/deactivate user ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new Versions('tl_user', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_user']['fields']['disable']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_user']['fields']['disable']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $this);
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_user SET tstamp=". time() .", disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_user.id='.$intId.'" has been created'.$this->getParentEntries('tl_user', $intId), __METHOD__, TL_GENERAL);

		// Remove the session if the user is disabled (see #5353)
		if (!$blnVisible)
		{
			$this->Database->prepare("DELETE FROM tl_session WHERE name='BE_USER_AUTH' AND pid=?")
						   ->execute($intId);
		}
	}
}
