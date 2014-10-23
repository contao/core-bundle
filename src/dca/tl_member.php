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
 * Table tl_member
 */
$GLOBALS['TL_DCA']['tl_member'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'onsubmit_callback' =>
		[
			['tl_member', 'storeDateAdded'],
			['tl_member', 'checkRemoveSession']
		],
		'ondelete_callback' =>
		[
			['tl_member', 'removeSession']
		],
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary',
				'username' => 'index',
				'email' => 'index',
				'autologin' => 'unique',
				'activation' => 'index'
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
			'fields'                  => ['icon', 'firstname', 'lastname', 'username', 'dateAdded'],
			'showColumns'             => true,
			'label_callback'          => ['tl_member', 'addIcon']
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
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'toggle' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => ['tl_member', 'toggleIcon']
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			],
			'su' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member']['su'],
				'href'                => 'key=su',
				'icon'                => 'su.gif',
				'button_callback'     => ['tl_member', 'switchUser']
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['login', 'assignDir'],
		'default'                     => '{personal_legend},firstname,lastname,dateOfBirth,gender;{address_legend:hide},company,street,postal,city,state,country;{contact_legend},phone,mobile,fax,email,website,language;{groups_legend},groups;{login_legend},login;{homedir_legend:hide},assignDir;{account_legend},disable,start,stop',
	],

	// Subpalettes
	'subpalettes' =>
	[
		'login'                       => 'username,password',
		'assignDir'                   => 'homeDir'
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
		'firstname' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['firstname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'lastname' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['lastname'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'dateOfBirth' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['dateOfBirth'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'date', 'datepicker'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(11) NOT NULL default ''"
		],
		'gender' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['gender'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['male', 'female'],
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => ['includeBlankOption'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'company' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['company'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'street' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['street'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'postal' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['postal'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>32, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'city' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['city'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'state' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['state'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>64, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'country' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['country'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'inputType'               => 'select',
			'options'                 => System::getCountries(),
			'eval'                    => ['includeBlankOption'=>true, 'chosen'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'address', 'tl_class'=>'w50'],
			'sql'                     => "varchar(2) NOT NULL default ''"
		],
		'phone' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['phone'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'mobile' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['mobile'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'fax' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['fax'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>64, 'rgxp'=>'phone', 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'email' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['email'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'email', 'unique'=>true, 'decodeEntities'=>true, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'website' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['website'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'url', 'maxlength'=>255, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'contact', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'language' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['language'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => System::getLanguages(),
			'eval'                    => ['includeBlankOption'=>true, 'chosen'=>true, 'rgxp'=>'locale', 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50'],
			'sql'                     => "varchar(5) NOT NULL default ''"
		],
		'groups' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['groups'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => ['multiple'=>true, 'feEditable'=>true, 'feGroup'=>'login'],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'belongsToMany', 'load'=>'lazy']
		],
		'login' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['login'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'username' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['username'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'unique'=>true, 'rgxp'=>'extnd', 'nospace'=>true, 'maxlength'=>64, 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'login'],
			'sql'                     => "varchar(64) COLLATE utf8_bin NOT NULL default ''"
		],
		'password' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['password'],
			'exclude'                 => true,
			'inputType'               => 'password',
			'eval'                    => ['mandatory'=>true, 'preserveTags'=>true, 'minlength'=>Config::get('minPasswordLength'), 'feEditable'=>true, 'feGroup'=>'login'],
			'save_callback' =>
			[
				['tl_member', 'setNewPassword']
			],
			'sql'                     => "varchar(128) NOT NULL default ''"
		],
		'assignDir' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['assignDir'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'homeDir' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['homeDir'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['fieldType'=>'radio', 'tl_class'=>'clr'],
			'sql'                     => "binary(16) NULL"
		],
		'disable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['disable'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'start' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		],
		'stop' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
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
			'eval'                    => ['rgxp'=>'datim'],
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
		],
		'session' =>
		[
			'eval'                    => ['doNotShow'=>true],
			'sql'                     => "blob NULL"
		],
		'autologin' =>
		[
			'default'                 => null,
			'eval'                    => ['doNotCopy'=>true],
			'sql'                     => "varchar(32) NULL"
		],
		'createdOn' =>
		[
			'eval'                    => ['rgxp'=>'datim'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'activation' =>
		[
			'eval'                    => ['doNotCopy'=>true],
			'sql'                     => "varchar(32) NOT NULL default ''"
		]
	]
];


/**
 * Filter disabled groups in the front end (see #6757)
 */
if (TL_MODE == 'FE')
{
	$GLOBALS['TL_DCA']['tl_member']['fields']['groups']['options_callback'] = ['tl_member', 'getActiveGroups'];
}


/**
 * Class tl_member
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_member extends Backend
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
	 * Filter disabled groups
	 * @return array
	 */
	public function getActiveGroups()
	{
		$arrGroups = [];
		$objGroup = MemberGroupModel::findAllActive();

		if ($objGroup !== null)
		{
			while ($objGroup->next())
			{
				$arrGroups[$objGroup->id] = $objGroup->name;
			}
		}

		return $arrGroups;
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
		$image = 'member';

		if ($row['disable'] || strlen($row['start']) && $row['start'] > time() || strlen($row['stop']) && $row['stop'] < time())
		{
			$image .= '_';
		}

		$args[0] = sprintf('<div class="list_icon_new" style="background-image:url(\'%ssystem/themes/%s/images/%s.gif\')">&nbsp;</div>', TL_ASSETS_URL, Backend::getTheme(), $image);
		return $args;
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

		return '<a href="contao/preview.php?user='.$row['username'].'" target="_blank" title="'.specialchars($title).'">'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Call the "setNewPassword" callback
	 * @param string
	 * @param object
	 * @return string
	 */
	public function setNewPassword($strPassword, $user)
	{
		// Return if there is no user (e.g. upon registration)
		if (!$user)
		{
			return $strPassword;
		}

		$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
								  ->limit(1)
								  ->execute($user->id);

		// HOOK: set new password callback
		if ($objUser->numRows)
		{
			if (isset($GLOBALS['TL_HOOKS']['setNewPassword']) && is_array($GLOBALS['TL_HOOKS']['setNewPassword']))
			{
				foreach ($GLOBALS['TL_HOOKS']['setNewPassword'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($objUser, $strPassword);
				}
			}
		}

		return $strPassword;
	}


	/**
	 * Store the date when the account has been added
	 * @param object
	 */
	public function storeDateAdded($dc)
	{
		// Front end call
		if (!$dc instanceof Contao\DataContainer)
		{
			return;
		}

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

		$this->Database->prepare("UPDATE tl_member SET dateAdded=? WHERE id=?")
					   ->execute($time, $dc->id);
	}


	/**
	 * Check whether the user session should be removed
	 * @param object
	 */
	public function checkRemoveSession($dc)
	{
		if ($dc instanceof Contao\DataContainer && $dc->activeRecord)
		{
			if ($dc->activeRecord->disable || ($dc->activeRecord->start != '' && $dc->activeRecord->start > time()) || ($dc->activeRecord->stop != '' && $dc->activeRecord->stop < time()))
			{
				$this->removeSession($dc);
			}
		}
	}


	/**
	 * Remove the session if a user is deleted (see #5353)
	 * @param object
	 */
	public function removeSession($dc)
	{
		if ($dc instanceof Contao\DataContainer && $dc->activeRecord)
		{
			$this->Database->prepare("DELETE FROM tl_session WHERE name='FE_USER_AUTH' AND pid=?")
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
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
			$this->redirect($this->getReferer());
		}

		// Check permissions AFTER checking the tid, so hacking attempts are logged
		if (!$this->User->hasAccess('tl_member::disable', 'alexf'))
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
	 * @param Contao\DataContainer
	 */
	public function toggleVisibility($intId, $blnVisible, Contao\DataContainer $dc=null)
	{
		// Check permissions
		if (!$this->User->hasAccess('tl_member::disable', 'alexf'))
		{
			$this->log('Not enough permissions to activate/deactivate member ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new Versions('tl_member', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_member']['fields']['disable']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_member']['fields']['disable']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, ($dc ?: $this));
				}
			}
		}

		$time = time();

		// Update the database
		$this->Database->prepare("UPDATE tl_member SET tstamp=$time, disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_member.id='.$intId.'" has been created'.$this->getParentEntries('tl_member', $intId), __METHOD__, TL_GENERAL);

		// Remove the session if the user is disabled (see #5353)
		if (!$blnVisible)
		{
			$this->Database->prepare("DELETE FROM tl_session WHERE name='FE_USER_AUTH' AND pid=?")
						   ->execute($intId);
		}

		// HOOK: update newsletter subscriptions
		if (in_array('newsletter', ModuleLoader::getActive()))
		{
			$objUser = $this->Database->prepare("SELECT email FROM tl_member WHERE id=?")
									  ->limit(1)
									  ->execute($intId);

			if ($objUser->numRows)
			{
				$this->Database->prepare("UPDATE tl_newsletter_recipients SET tstamp=$time, active=? WHERE email=?")
							   ->execute(($blnVisible ? 1 : ''), $objUser->email);
			}
		}
	}
}
