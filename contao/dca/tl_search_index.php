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
 * Table tl_search_index
 */
$GLOBALS['TL_DCA']['tl_search_index'] =
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
				'word' => 'index'
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
		'word' =>
		[
			'sql'                     => "varchar(64) COLLATE utf8_bin NOT NULL default ''"
		],
		'relevance' =>
		[
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'language' =>
		[
			'sql'                     => "varchar(5) NOT NULL default ''"
		]
	]
];
