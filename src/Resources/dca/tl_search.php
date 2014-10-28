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
 * Table tl_search
 */
$GLOBALS['TL_DCA']['tl_search'] =
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
				'url' => 'index',
				'text' => 'fulltext'
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
		'title' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'url' =>
		[
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'text' =>
		[
			'sql'                     => "mediumtext NULL"
		],
		'filesize' =>
		[
			'sql'                     => "double unsigned NOT NULL default '0'"
		],
		'checksum' =>
		[
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'protected' =>
		[
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'groups' =>
		[
			'sql'                     => "blob NULL"
		],
		'language' =>
		[
			'sql'                     => "varchar(5) NOT NULL default ''"
		]
	]
];
