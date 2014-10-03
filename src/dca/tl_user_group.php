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
 * Load tl_user language file
 */
System::loadLanguageFile('tl_user');


/**
 * Table tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary'
			]
		]
	],

	// List
	'list' =>
	[
		'sorting' =>
		[
			'mode'                    => 1,
			'fields'                  => ['name'],
			'flag'                    => 1,
			'panelLayout'             => 'filter,search,limit',
		],
		'label' =>
		[
			'fields'                  => ['name'],
			'format'                  => '%s',
			'label_callback'          => ['tl_user_group', 'addIcon']
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
				'label'               => &$GLOBALS['TL_LANG']['tl_user_group']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user_group']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user_group']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'toggle' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user_group']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => ['tl_user_group', 'toggleIcon']
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_user_group']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'default'                     => '{title_legend},name;{modules_legend},modules,themes;{pagemounts_legend},pagemounts,alpty;{filemounts_legend},filemounts,fop;{forms_legend},forms,formp;{alexf_legend:hide},alexf;{account_legend},disable,start,stop',
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
		'name' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'unique'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'modules' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['modules'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => ['tl_user_group', 'getModules'],
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => ['multiple'=>true, 'helpwizard'=>true],
			'sql'                     => "blob NULL"
		],
		'themes' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user']['themes'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'                 => ['css', 'modules', 'layout', 'image_sizes', 'theme_import', 'theme_export'],
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
			'exclude'                 => true,
			'default'                 => ['f1', 'f2', 'f3'],
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
		'alexf' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['alexf'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => ['tl_user_group', 'getExcludedFields'],
			'eval'                    => ['multiple'=>true, 'size'=>36],
			'sql'                     => "blob NULL"
		],
		'disable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['disable'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'start' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		],
		'stop' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		]
	]
];


/**
 * Class tl_user_group
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_user_group extends Backend
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
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function addIcon($row, $label)
	{
		$image = 'group';

		if ($row['disable'] || strlen($row['start']) && $row['start'] > time() || strlen($row['stop']) && $row['stop'] < time())
		{
			$image .= '_';
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'%ssystem/themes/%s/images/%s.gif\')">%s</div>', TL_ASSETS_URL, Backend::getTheme(), $image, $label);
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
	 * Return all excluded fields as HTML drop down menu
	 * @return array
	 */
	public function getExcludedFields()
	{
		$included = [];

		foreach (ModuleLoader::getActive() as $strModule)
		{
			$strDir = 'system/modules/' . $strModule . '/dca';

			if (!is_dir(TL_ROOT . '/' . $strDir))
			{
				continue;
			}

			foreach (scan(TL_ROOT . '/' . $strDir) as $strFile)
			{
				// Ignore non PHP files and files which have been included before
				if (substr($strFile, -4) != '.php' || in_array($strFile, $included))
				{
					continue;
				}

				$included[] = $strFile;
				$strTable = substr($strFile, 0, -4);

				System::loadLanguageFile($strTable);
				$this->loadDataContainer($strTable);
			}
		}

		$arrReturn = [];

		// Get all excluded fields
		foreach ($GLOBALS['TL_DCA'] as $k=>$v)
		{
			if (is_array($v['fields']))
			{
				foreach ($v['fields'] as $kk=>$vv)
				{
					if ($vv['exclude'] || $vv['orig_exclude'])
					{
						$arrReturn[$k][specialchars($k.'::'.$kk)] = $vv['label'][0] ?: $kk;
					}
				}
			}
		}

		ksort($arrReturn);
		return $arrReturn;
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
		if (!$this->User->hasAccess('tl_user_group::disable', 'alexf'))
		{
			return '';
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.$row['disable'];

		if ($row['disable'])
		{
			$icon = 'invisible.gif';
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
		// Check permissions
		if (!$this->User->hasAccess('tl_user_group::disable', 'alexf'))
		{
			$this->log('Not enough permissions to activate/deactivate user group ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new Versions('tl_user_group', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_user_group']['fields']['disable']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_user_group']['fields']['disable']['save_callback'] as $callback)
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
		$this->Database->prepare("UPDATE tl_user_group SET tstamp=". time() .", disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_user_group.id='.$intId.'" has been created'.$this->getParentEntries('tl_user_group', $intId), __METHOD__, TL_GENERAL);
	}
}
