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
 * Table tl_session
 */
$GLOBALS['TL_DCA']['tl_session'] =
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
				'hash' => 'unique'
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
		'name' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'sessionID' =>
		[
			'sql'                     => "varchar(128) NOT NULL default ''"
		],
		'hash' =>
		[
			'sql'                     => "varchar(40) NULL"
		],
		'ip' =>
		[
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'su' =>
		[
			'sql'                     => "char(1) NOT NULL default ''"
		]
	]
];
