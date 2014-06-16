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
 * Table tl_cron
 */
$GLOBALS['TL_DCA']['tl_cron'] =
[

	// Config
	'config' =>
	[
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary',
				'name' => 'unique'
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
		'name' =>
		[
			'sql'                     => "varchar(32) NULL"
		],
		'value' =>
		[
			'sql'                     => "varchar(32) NOT NULL default ''"
		]
	]
];
