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
 * Table tl_layout
 */
$GLOBALS['TL_DCA']['tl_layout'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_theme',
		'enableVersioning'            => true,
		'onload_callback' =>
		[
			['tl_layout', 'checkPermission']
		],
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
			'mode'                    => 4,
			'fields'                  => ['name'],
			'panelLayout'             => 'filter;sort,search,limit',
			'headerFields'            => ['name', 'author', 'tstamp'],
			'child_record_callback'   => ['tl_layout', 'listLayout'],
			'child_record_class'      => 'no_padding'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_layout']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_layout']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif'
			],
			'cut' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_layout']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_layout']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_layout']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['rows', 'cols', 'addJQuery', 'addMooTools', 'static'],
		'default'                     => '{title_legend},name;{header_legend},rows;{column_legend},cols;{sections_legend:hide},sections,sPosition;{webfonts_legend:hide},webfonts;{style_legend},framework,stylesheet,external,loadingOrder;{picturefill_legend:hide},picturefill;{feed_legend:hide},newsfeeds,calendarfeeds;{modules_legend},modules;{jquery_legend:hide},addJQuery;{mootools_legend:hide},addMooTools;{script_legend},scripts,analytics,script;{static_legend:hide},static;{expert_legend:hide},template,doctype,viewport,titleTag,cssClass,onload,head'
	],

	// Subpalettes
	'subpalettes' =>
	[
		'rows_2rwh'                   => 'headerHeight',
		'rows_2rwf'                   => 'footerHeight',
		'rows_3rw'                    => 'headerHeight,footerHeight',
		'cols_2cll'                   => 'widthLeft',
		'cols_2clr'                   => 'widthRight',
		'cols_3cl'                    => 'widthLeft,widthRight',
		'addJQuery'                   => 'jSource,jquery',
		'addMooTools'                 => 'mooSource,mootools',
		'static'                      => 'width,align'
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
			'foreignKey'              => 'tl_theme.name',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'belongsTo', 'load'=>'eager']
		],
		'tstamp' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'name' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['name'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'rows' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['rows'],
			'default'                 => '2rwh',
			'exclude'                 => true,
			'inputType'               => 'radioTable',
			'options'                 => ['1rw', '2rwh', '2rwf', '3rw'],
			'eval'                    => ['helpwizard'=>true, 'cols'=>4, 'submitOnChange'=>true],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(8) NOT NULL default ''"
		],
		'headerHeight' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['headerHeight'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
			'eval'                    => ['includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'footerHeight' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['footerHeight'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
			'eval'                    => ['includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'cols' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['cols'],
			'default'                 => '2cll',
			'exclude'                 => true,
			'inputType'               => 'radioTable',
			'options'                 => ['1cl', '2cll', '2clr', '3cl'],
			'eval'                    => ['helpwizard'=>true, 'cols'=>4, 'submitOnChange'=>true],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(8) NOT NULL default ''"
		],
		'widthLeft' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['widthLeft'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
			'eval'                    => ['includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'widthRight' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['widthRight'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
			'eval'                    => ['includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'sections' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['sections'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(1022) NOT NULL default ''"
		],
		'sPosition' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['sPosition'],
			'default'                 => 'main',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['top', 'before', 'main', 'after', 'bottom'],
			'eval'                    => ['tl_class'=>'w50'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'framework' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['framework'],
			'default'                 => ['layout.css', 'responsive.css'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options'                 => ['layout.css', 'responsive.css', 'grid.css', 'reset.css', 'form.css', 'tinymce.css'],
			'eval'                    => ['multiple'=>true, 'helpwizard'=>true],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'stylesheet' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['stylesheet'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkboxWizard',
			'foreignKey'              => 'tl_style_sheet.name',
			'options_callback'        => ['tl_layout', 'getStyleSheets'],
			'eval'                    => ['multiple'=>true],
			'xlabel' =>
			[
				['tl_layout', 'styleSheetLink']
			],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'hasMany', 'load'=>'lazy']
		],
		'external' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['external'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['multiple'=>true, 'orderField'=>'orderExt', 'fieldType'=>'checkbox', 'filesOnly'=>true, 'extensions'=>'css,scss,less'],
			'sql'                     => "blob NULL"
		],
		'orderExt' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['orderExt'],
			'sql'                     => "blob NULL"
		],
		'loadingOrder' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['loadingOrder'],
			'default'                 => 'external_first',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['external_first', 'internal_first'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(16) NOT NULL default ''"
		],
		'newsfeeds' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['newsfeeds'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => ['tl_layout', 'getNewsfeeds'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'calendarfeeds' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['calendarfeeds'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options_callback'        => ['tl_layout', 'getCalendarfeeds'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'modules' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['modules'],
			'default'                 => [['mod'=>0, 'col'=>'main', 'enable'=>1]],
			'exclude'                 => true,
			'inputType'               => 'moduleWizard',
			'sql'                     => "blob NULL"
		],
		'template' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['template'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'inputType'               => 'select',
			'options_callback'        => ['tl_layout', 'getPageTemplates'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'doctype' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['doctype'],
			'exclude'                 => true,
			'filter'                  => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'inputType'               => 'select',
			'options'                 => ['html5'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'webfonts' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['webfonts'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'viewport' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['viewport'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'titleTag' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['titleTag'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'cssClass' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['cssClass'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'onload' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['onload'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'head' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['head'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['style'=>'height:60px', 'preserveTags'=>true, 'rte'=>'ace|html', 'tl_class'=>'clr'],
			'sql'                     => "text NULL"
		],
		'addJQuery' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['addJQuery'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'jSource' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['jSource'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['j_local', 'j_googleapis', 'j_fallback'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(16) NOT NULL default ''"
		],
		'jquery' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['jquery'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => ['tl_layout', 'getJqueryTemplates'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "text NULL"
		],
		'addMooTools' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['addMooTools'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'mooSource' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['mooSource'],
			'default'                 => 'moo_local',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['moo_local', 'moo_googleapis', 'moo_fallback'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'sql'                     => "varchar(16) NOT NULL default ''"
		],
		'mootools' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['mootools'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => ['tl_layout', 'getMooToolsTemplates'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "text NULL"
		],
		'picturefill' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['picturefill'],
			'exclude'                 => true,
			'inputType'               => 'select',
            'options'                 => ['picturefill.js', 'respimage.js'],
            'eval'                    => ['includeBlankOption'=>true],
			'sql'                     => "varchar(16) NOT NULL default ''"
		],
		'analytics' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['analytics'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => ['tl_layout', 'getAnalyticsTemplates'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_layout'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "text NULL"
		],
		'scripts' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['scripts'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => ['tl_layout', 'getScriptTemplates'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "text NULL"
		],
		'script' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['script'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['style'=>'height:120px', 'preserveTags'=>true, 'rte'=>'ace|html', 'tl_class'=>'clr'],
			'sql'                     => "text NULL"
		],
		'static' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['static'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'width' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['width'],
			'exclude'                 => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['px', '%', 'em', 'rem', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'],
			'eval'                    => ['includeBlankOption'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'align' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['align'],
			'default'                 => 'center',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['left', 'center', 'right'],
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		]
	]
];


/**
 * Class tl_layout
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_layout extends Backend
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
	 * Check permissions to edit the table
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		if (!$this->User->hasAccess('layout', 'themes'))
		{
			$this->log('Not enough permissions to access the page layout module', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	}


	/**
	 * Return all style sheets of the current theme
	 * @param Contao\DataContainer
	 * @return array
	 */
	public function getStyleSheets(Contao\DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if (Input::get('act') == 'overrideAll')
		{
			$intPid = Input::get('id');
		}

		$objStyleSheet = $this->Database->prepare("SELECT id, name FROM tl_style_sheet WHERE pid=?")
										->execute($intPid);

		if ($objStyleSheet->numRows < 1)
		{
			return [];
		}

		$return = [];

		while ($objStyleSheet->next())
		{
			$return[$objStyleSheet->id] = $objStyleSheet->name;
		}

		return $return;
	}


	/**
	 * Return all news archives with XML feeds
	 * @return array
	 */
	public function getNewsfeeds()
	{
		if (!in_array('news', ModuleLoader::getActive()))
		{
			return [];
		}

		$objFeed = NewsFeedModel::findAll();

		if ($objFeed === null)
		{
			return [];
		}

		$return = [];

		while ($objFeed->next())
		{
			$return[$objFeed->id] = $objFeed->title;
		}

		return $return;
	}


	/**
	 * Return all calendars with XML feeds
	 * @return array
	 */
	public function getCalendarfeeds()
	{
		if (!in_array('calendar', ModuleLoader::getActive()))
		{
			return [];
		}

		$objFeed = CalendarFeedModel::findAll();

		if ($objFeed === null)
		{
			return [];
		}

		$return = [];

		while ($objFeed->next())
		{
			$return[$objFeed->id] = $objFeed->title;
		}

		return $return;
	}


	/**
	 * Return all page templates as array
	 * @return array
	 */
	public function getPageTemplates()
	{
		return $this->getTemplateGroup('fe_');
	}


	/**
	 * Return all MooTools templates as array
	 * @return array
	 */
	public function getMooToolsTemplates()
	{
		return $this->getTemplateGroup('moo_');
	}


	/**
	 * Return all jQuery templates as array
	 * @return array
	 */
	public function getJqueryTemplates()
	{
		return $this->getTemplateGroup('j_');
	}


	/**
	 * Return all script templates as array
	 * @return array
	 */
	public function getScriptTemplates()
	{
		return $this->getTemplateGroup('js_');
	}


	/**
	 * Return all analytics templates as array
	 * @return array
	 */
	public function getAnalyticsTemplates()
	{
		return $this->getTemplateGroup('analytics_');
	}


	/**
	 * List a page layout
	 * @param array
	 * @return string
	 */
	public function listLayout($row)
	{
		return '<div style="float:left">'. $row['name'] ."</div>\n";
	}


	/**
	 * Add a link to edit the stylesheets of the theme
	 * @param Contao\DataContainer
	 * @return string
	 */
	public function styleSheetLink(Contao\DataContainer $dc)
	{
		return ' <a href="contao/main.php?do=themes&amp;table=tl_style_sheet&amp;id=' . $dc->activeRecord->pid . '&amp;popup=1&amp;rt=' . REQUEST_TOKEN . '" title="' . specialchars($GLOBALS['TL_LANG']['tl_layout']['edit_styles']) . '" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''.specialchars(str_replace("'", "\\'", $GLOBALS['TL_LANG']['tl_layout']['edit_styles'])).'\',\'url\':this.href});return false"><img width="12" height="16" alt="" src="system/themes/' . Backend::getTheme() . '/images/edit.gif" style="vertical-align:text-bottom"></a>';
	}
}
