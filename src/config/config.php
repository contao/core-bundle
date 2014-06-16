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
 * Back end modules
 */
$GLOBALS['BE_MOD'] =
[
	// Content modules
	'content' =>
	[
		'article' =>
		[
			'tables'      => ['tl_article', 'tl_content'],
			'table'       => ['TableWizard', 'importTable'],
			'list'        => ['ListWizard', 'importList']
		],
		'form' =>
		[
			'tables'      => ['tl_form', 'tl_form_field']
		]
	],

	// Design modules
	'design' =>
	[
		'themes' =>
		[
			'tables'      => ['tl_theme', 'tl_module', 'tl_style_sheet', 'tl_style', 'tl_layout'],
			'importTheme' => ['Theme', 'importTheme'],
			'exportTheme' => ['Theme', 'exportTheme'],
			'import'      => ['StyleSheets', 'importStyleSheet']
		],
		'page' =>
		[
			'tables'      => ['tl_page']
		],
		'tpl_editor' =>
		[
			'tables'      => ['tl_templates'],
			'new_tpl'     => ['tl_templates', 'addNewTemplate']
		]
	],

	// Account modules
	'accounts' =>
	[
		'member' =>
		[
			'tables'      => ['tl_member']
		],
		'mgroup' =>
		[
			'tables'      => ['tl_member_group']
		],
		'user' =>
		[
			'tables'      => ['tl_user']
		],
		'group' =>
		[
			'tables'      => ['tl_user_group']
		]
	],

	// System modules
	'system' =>
	[
		'files' =>
		[
			'tables'      => ['tl_files']
		],
		'log' =>
		[
			'tables'      => ['tl_log']
		],
		'settings' =>
		[
			'tables'      => ['tl_settings']
		],
		'maintenance' =>
		[
			'callback'    => 'Contao\\ModuleMaintenance'
		],
		'undo' =>
		[
			'tables'      => ['tl_undo']
		]
	]
];


/**
 * Front end modules
 */
$GLOBALS['FE_MOD'] =
[
	'navigationMenu' =>
	[
		'navigation'     => 'ModuleNavigation',
		'customnav'      => 'ModuleCustomnav',
		'breadcrumb'     => 'ModuleBreadcrumb',
		'quicknav'       => 'ModuleQuicknav',
		'quicklink'      => 'ModuleQuicklink',
		'booknav'        => 'ModuleBooknav',
		'articlenav'     => 'ModuleArticlenav',
		'sitemap'        => 'ModuleSitemap'
	],
	'user' =>
	[
		'login'          => 'ModuleLogin',
		'logout'         => 'ModuleLogout',
		'personalData'   => 'ModulePersonalData',
		'registration'   => 'ModuleRegistration',
		'lostPassword'   => 'ModulePassword',
		'closeAccount'   => 'ModuleCloseAccount'
	],
	'application' =>
	[
		'form'           => 'Form',
		'search'         => 'ModuleSearch'
	],
	'miscellaneous' =>
	[
		'flash'          => 'ModuleFlash',
		'articleList'    => 'ModuleArticleList',
		'randomImage'    => 'ModuleRandomImage',
		'html'           => 'ModuleHtml',
		'rss_reader'     => 'ModuleRssReader'
	]
];


/**
 * Content elements
 */
$GLOBALS['TL_CTE'] =
[
	'texts' =>
	[
		'headline'        => 'ContentHeadline',
		'text'            => 'ContentText',
		'html'            => 'ContentHtml',
		'list'            => 'ContentList',
		'table'           => 'ContentTable',
		'code'            => 'ContentCode',
		'markdown'        => 'ContentMarkdown'
	],
	'accordion' =>
	[
		'accordionSingle' => 'ContentAccordion',
		'accordionStart'  => 'ContentAccordionStart',
		'accordionStop'   => 'ContentAccordionStop'
	],
	'slider' =>
	[
		'sliderStart'     => 'ContentSliderStart',
		'sliderStop'      => 'ContentSliderStop'
	],
	'links' =>
	[
		'hyperlink'       => 'ContentHyperlink',
		'toplink'         => 'ContentToplink'
	],
	'media' =>
	[
		'image'           => 'ContentImage',
		'gallery'         => 'ContentGallery',
		'player'          => 'ContentMedia',
		'youtube'         => 'ContentYouTube'
	],
	'files' =>
	[
		'download'        => 'ContentDownload',
		'downloads'       => 'ContentDownloads'
	],
	'includes' =>
	[
		'article'         => 'ContentArticle',
		'alias'           => 'ContentAlias',
		'form'            => 'Form',
		'module'          => 'ContentModule',
		'teaser'          => 'ContentTeaser'
	]
];


/**
 * Back end form fields
 */
$GLOBALS['BE_FFL'] =
[
	'text'           => 'TextField',
	'password'       => 'Password',
	'textStore'      => 'TextStore',
	'textarea'       => 'TextArea',
	'select'         => 'SelectMenu',
	'checkbox'       => 'CheckBox',
	'checkboxWizard' => 'CheckBoxWizard',
	'radio'          => 'RadioButton',
	'radioTable'     => 'RadioTable',
	'inputUnit'      => 'InputUnit',
	'trbl'           => 'TrblField',
	'chmod'          => 'ChmodTable',
	'pageTree'       => 'PageTree',
	'pageSelector'   => 'PageSelector',
	'fileTree'       => 'FileTree',
	'fileSelector'   => 'FileSelector',
	'fileUpload'     => 'Upload',
	'tableWizard'    => 'TableWizard',
	'listWizard'     => 'ListWizard',
	'optionWizard'   => 'OptionWizard',
	'moduleWizard'   => 'ModuleWizard',
	'keyValueWizard' => 'KeyValueWizard',
	'imageSize'      => 'ImageSize',
	'timePeriod'     => 'TimePeriod',
	'metaWizard'     => 'MetaWizard'
];


/**
 * Front end form fields
 */
$GLOBALS['TL_FFL'] =
[
	'headline'    => 'FormHeadline',
	'explanation' => 'FormExplanation',
	'html'        => 'FormHtml',
	'fieldset'    => 'FormFieldset',
	'text'        => 'FormTextField',
	'password'    => 'FormPassword',
	'textarea'    => 'FormTextArea',
	'select'      => 'FormSelectMenu',
	'radio'       => 'FormRadioButton',
	'checkbox'    => 'FormCheckBox',
	'upload'      => 'FormFileUpload',
	'hidden'      => 'FormHidden',
	'captcha'     => 'FormCaptcha',
	'submit'      => 'FormSubmit'
];


/**
 * Page types
 */
$GLOBALS['TL_PTY'] =
[
	'regular'   => 'PageRegular',
	'forward'   => 'PageForward',
	'redirect'  => 'PageRedirect',
	'root'      => 'PageRoot',
	'error_403' => 'PageError403',
	'error_404' => 'PageError404'
];


/**
 * Maintenance
 */
$GLOBALS['TL_MAINTENANCE'] =
[
	'LiveUpdate',
	'RebuildIndex',
	'PurgeData'
];


/**
 * Purge jobs
 */
$GLOBALS['TL_PURGE'] =
[
	'tables' =>
	[
		'index' =>
		[
			'callback' => ['Automator', 'purgeSearchTables'],
			'affected' => ['tl_search', 'tl_search_index']
		],
		'undo' =>
		[
			'callback' => ['Automator', 'purgeUndoTable'],
			'affected' => ['tl_undo']
		],
		'versions' =>
		[
			'callback' => ['Automator', 'purgeVersionTable'],
			'affected' => ['tl_version']
		],
		'log' =>
		[
			'callback' => ['Automator', 'purgeSystemLog'],
			'affected' => ['tl_log']
		]
	],
	'folders' =>
	[
		'images' =>
		[
			'callback' => ['Automator', 'purgeImageCache'],
			'affected' => ['assets/images']
		],
		'scripts' =>
		[
			'callback' => ['Automator', 'purgeScriptCache'],
			'affected' => ['assets/js', 'assets/css']
		],
		'pages' =>
		[
			'callback' => ['Automator', 'purgePageCache'],
			'affected' => ['system/cache/html']
		],
		'search' =>
		[
			'callback' => ['Automator', 'purgeSearchCache'],
			'affected' => ['system/cache/search']
		],
		'internal' =>
		[
			'callback' => ['Automator', 'purgeInternalCache'],
			'affected' => ['system/cache/config', 'system/cache/dca', 'system/cache/language', 'system/cache/sql']
		],
		'temp' =>
		[
			'callback' => ['Automator', 'purgeTempFolder'],
			'affected' => ['system/tmp']
		]
	],
	'custom' =>
	[
		'xml' =>
		[
			'callback' => ['Automator', 'generateXmlFiles']
		],
		'symlinks' =>
		[
			'callback' => ['Automator', 'generateSymlinks']
		]
	]
];


/**
 * Image crop modes
 */
$GLOBALS['TL_CROP'] =
[
	'relative' =>
	[
		'proportional', 'box'
	],
	'crop' =>
	[
		'left_top',    'center_top',    'right_top',
		'left_center', 'center_center', 'right_center',
		'left_bottom', 'center_bottom', 'right_bottom'
	]
];


/**
 * Cron jobs
 */
$GLOBALS['TL_CRON'] =
[
	'monthly' =>
	[
		['Automator', 'purgeImageCache']
	],
	'weekly' =>
	[
		['Automator', 'generateSitemap'],
		['Automator', 'purgeScriptCache'],
		['Automator', 'purgeSearchCache']
	],
	'daily' =>
	[
		['Automator', 'rotateLogs'],
		['Automator', 'purgeTempFolder'],
		['Automator', 'checkForUpdates']
	],
	'hourly' => [],
	'minutely' => []
];


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS'] =
[
	'getSystemMessages' =>
	[
		['Messages', 'versionCheck'],
		['Messages', 'lastLogin'],
		['Messages', 'topLevelRoot'],
		['Messages', 'languageFallback']
	]
];


/**
 * Register the auto_item keywords
 */
$GLOBALS['TL_AUTO_ITEM'] = ['items', 'events'];


/**
 * Do not index a page if one of the following parameters is set
 */
$GLOBALS['TL_NOINDEX_KEYS'] = ['id', 'file', 'token', 'day', 'month', 'year', 'page', 'PHPSESSID'];


/**
 * Wrapper elements
 */
$GLOBALS['TL_WRAPPERS'] =
[
	'start' =>
	[
		'accordionStart',
		'sliderStart'
	],
	'stop' =>
	[
		'accordionStop',
		'sliderStop'
	],
	'single' =>
	[
		'accordionSingle'
	],
	'separator' => []
];


/**
 * Asset versions
 */
$GLOBALS['TL_ASSETS'] =
[
	'ACE'          => '1.1.3',
	'CSS3PIE'      => '1.0.0',
	'DROPZONE'     => '3.8.5',
	'HIGHLIGHTER'  => '3.0.83',
	'HTML5SHIV'    => '3.7.0',
	'SWIPE'        => '2.0',
	'JQUERY'       => '1.11.0',
	'JQUERY_UI'    => '1.10.4',
	'COLORBOX'     => '1.5.8',
	'MEDIAELEMENT' => '2.14.2',
	'TABLESORTER'  => '2.0.5',
	'MOOTOOLS'     => '1.5.0',
	'COLORPICKER'  => '1.4',
	'DATEPICKER'   => '2.2.0',
	'MEDIABOX'     => '1.4.6',
	'SIMPLEMODAL'  => '1.2',
	'SLIMBOX'      => '1.8'
];


/**
 * Other global arrays
 */
$GLOBALS['TL_MIME'] = [];
$GLOBALS['TL_PERMISSIONS'] = [];
$GLOBALS['TL_MODELS'] = [];
