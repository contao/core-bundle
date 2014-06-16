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
 * Table tl_log
 */
$GLOBALS['TL_DCA']['tl_log'] =
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
		]
	],

	// List
	'list'  =>
	[
		'sorting' =>
		[
			'mode'                    => 2,
			'fields'                  => ['tstamp DESC', 'id DESC'],
			'panelLayout'             => 'filter;sort,search,limit'
		],
		'label' =>
		[
			'fields'                  => ['tstamp', 'text'],
			'format'                  => '<span style="color:#b3b3b3;padding-right:3px">[%s]</span> %s',
			'label_callback'          => ['tl_log', 'colorize']
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
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_log']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_log']['show'],
				'href'                => 'act=show',
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
		'tstamp' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['tstamp'],
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 6,
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'source' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['source'],
			'filter'                  => true,
			'sorting'                 => true,
			'reference'               => &$GLOBALS['TL_LANG']['tl_log'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'action' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['action'],
			'filter'                  => true,
			'sorting'                 => true,
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'username' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['username'],
			'search'                  => true,
			'filter'                  => true,
			'sorting'                 => true,
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'text' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['text'],
			'search'                  => true,
			'sql'                     => "text NULL"
		],
		'func' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['func'],
			'sorting'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'ip' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['ip'],
			'sorting'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'browser' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_log']['browser'],
			'sorting'                 => true,
			'search'                  => true,
			'sql'                     => "varchar(255) NOT NULL default ''"
		]
	]
];


/**
 * Class tl_log
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_log extends Backend
{

	/**
	 * Colorize the log entries depending on their category
	 * @param array
	 * @param string
	 * @return string
	 */
	public function colorize($row, $label)
	{
		switch ($row['action'])
		{
			case 'CONFIGURATION':
			case 'REPOSITORY':
				$label = preg_replace('@^(.*</span> )(.*)$@U', '$1 <span class="tl_blue">$2</span>', $label);
				break;

			case 'CRON':
				$label = preg_replace('@^(.*</span> )(.*)$@U', '$1 <span class="tl_green">$2</span>', $label);
				break;

			case 'ERROR':
				$label = preg_replace('@^(.*</span> )(.*)$@U', '$1 <span class="tl_red">$2</span>', $label);
				break;

			default:
				if (isset($GLOBALS['TL_HOOKS']['colorizeLogEntries']) && is_array($GLOBALS['TL_HOOKS']['colorizeLogEntries']))
				{
					foreach ($GLOBALS['TL_HOOKS']['colorizeLogEntries'] as $callback)
					{
						$this->import($callback[0]);
						$label = $this->$callback[0]->$callback[1]($row, $label);
					}
				}
				break;
		}

		return '<div class="ellipsis">' . $label . '</div>';
	}
}
