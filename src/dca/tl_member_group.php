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
 * Table tl_member_group
 */
$GLOBALS['TL_DCA']['tl_member_group'] =
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
			'label_callback'          => ['tl_member_group', 'addIcon']
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
				'label'               => &$GLOBALS['TL_LANG']['tl_member_group']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member_group']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member_group']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'toggle' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member_group']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => ['tl_member_group', 'toggleIcon']
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_member_group']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['redirect'],
		'default'                     => '{title_legend},name;{redirect_legend:hide},redirect;{disable_legend},disable,start,stop',
	],

	// Subpalettes
	'subpalettes' =>
	[
		'redirect'                    => 'jumpTo',
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'unique'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'redirect' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['redirect'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'jumpTo' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'foreignKey'              => 'tl_page.title',
			'eval'                    => ['mandatory'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'hasOne', 'load'=>'eager']
		],
		'disable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['disable'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'start' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		],
		'stop' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_member_group']['stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
			'sql'                     => "varchar(10) NOT NULL default ''"
		]
	]
];


/**
 * Class tl_member_group
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_member_group extends Backend
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
		$image = 'mgroup';

		if ($row['disable'] || strlen($row['start']) && $row['start'] > time() || strlen($row['stop']) && $row['stop'] < time())
		{
			$image .= '_';
		}

		return sprintf('<div class="list_icon" style="background-image:url(\'%ssystem/themes/%s/images/%s.gif\')">%s</div>', TL_ASSETS_URL, Backend::getTheme(), $image, $label);
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
		if (!$this->User->hasAccess('tl_member_group::disable', 'alexf'))
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
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions
		if (!$this->User->hasAccess('tl_member_group::disable', 'alexf'))
		{
			$this->log('Not enough permissions to activate/deactivate member group ID "'.$intId.'"', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$objVersions = new Versions('tl_member_group', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_member_group']['fields']['disable']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_member_group']['fields']['disable']['save_callback'] as $callback)
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
		$this->Database->prepare("UPDATE tl_member_group SET tstamp=" . time() .", disable='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_member_group.id='.$intId.'" has been created'.$this->getParentEntries('tl_member_group', $intId), __METHOD__, TL_GENERAL);
	}
}
