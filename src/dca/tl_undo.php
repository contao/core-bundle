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
 * Table tl_undo
 */
$GLOBALS['TL_DCA']['tl_undo'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'closed'                      => true,
		'notEditable'                 => true,
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary'
			]
		],
		'onload_callback' =>
		[
			['tl_undo', 'checkPermission']
		]
	],

	// List
	'list'  =>
	[
		'sorting' =>
		[
			'mode'                    => 2,
			'fields'                  => ['tstamp DESC'],
			'panelLayout'             => 'sort,search,limit'
		],
		'label' =>
		[
			'fields'                  => ['tstamp', 'query'],
			'format'                  => '<span style="color:#b3b3b3;padding-right:3px">[%s]</span>%s',
			'label_callback'          => ['tl_undo', 'ellipsis']
		],
		'operations' =>
		[
			'undo' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_undo']['undo'],
				'href'                => '&amp;act=undo',
				'icon'                => 'undo.gif'
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_undo']['show'],
				'href'                => '&amp;act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Fields
	'fields' =>
	[
		'id' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		],
		'pid' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_undo']['pid'],
			'sorting'                 => true,
			'foreignKey'              => 'tl_user.name',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'belongsTo', 'load'=>'lazy']
		],
		'tstamp' =>
		[
			'sorting'                 => true,
			'flag'                    => 6,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'fromTable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_undo']['fromTable'],
			'sorting'                 => true,
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'query' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_undo']['query'],
			'sql'                     => "text NULL"
		],
		'affectedRows' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_undo']['affectedRows'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'data' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_undo']['data'],
			'search'                  => true,
			'sql'                     => "mediumblob NULL"
		]
	]
];


/**
 * Class tl_undo
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_undo extends Backend
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
	 * Check permissions to use table tl_undo
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Show only own undo steps
		$objSteps = $this->Database->prepare("SELECT id FROM tl_undo WHERE pid=?")
								   ->execute($this->User->id);

		// Restrict the list
		$GLOBALS['TL_DCA']['tl_undo']['list']['sorting']['root'] = $objSteps->numRows ? $objSteps->fetchEach('id') : [0];

		// Redirect if there is an error
		if (Input::get('act') && !in_array(Input::get('id'), $GLOBALS['TL_DCA']['tl_undo']['list']['sorting']['root']))
		{
			$this->log('Not enough permissions to '. Input::get('act') .' undo step ID '. Input::get('id'), __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	}


	/**
	 * Add the surrounding ellipsis layer
	 * @param array
	 * @param string
	 * @return string
	 */
	public function ellipsis($row, $label)
	{
		return '<div class="ellipsis">' . $label . '</div>';
	}
}
