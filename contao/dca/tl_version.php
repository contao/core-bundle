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
 * Table tl_version
 */
$GLOBALS['TL_DCA']['tl_version'] =
[

	// Config
	'config' =>
	[
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary',
				'pid' => 'index',
				'fromTable' => 'index'
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
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'tstamp' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'version' =>
		[
			'sql'                     => "smallint(5) unsigned NOT NULL default '1'"
		],
		'fromTable' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'userid' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'username' =>
		[
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'description' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'editUrl' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'active' =>
		[
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'data' =>
		[
			'sql'                     => "mediumblob NULL"
		]
	]
];
