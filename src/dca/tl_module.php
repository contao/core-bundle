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
 * Table tl_module
 */
$GLOBALS['TL_DCA']['tl_module'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_theme',
		'enableVersioning'            => true,
		'onload_callback' =>
		[
			['tl_module', 'checkPermission']
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
			'child_record_callback'   => ['tl_module', 'listModule'],
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
				'label'               => &$GLOBALS['TL_LANG']['tl_module']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_module']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			],
			'cut' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_module']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_module']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_module']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['type', 'defineRoot', 'source', 'interactive', 'protected', 'reg_assignDir', 'reg_activate'],
		'default'                     => '{title_legend},name,type',
		'navigation'                  => '{title_legend},name,headline,type;{nav_legend},levelOffset,showLevel,hardLimit,showProtected;{reference_legend:hide},defineRoot;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'customnav'                   => '{title_legend},name,headline,type;{nav_legend},pages,showProtected;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'breadcrumb'                  => '{title_legend},name,headline,type;{nav_legend},showHidden;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'quicknav'                    => '{title_legend},name,headline,type;{nav_legend},customLabel,showLevel,hardLimit,showProtected,showHidden;{reference_legend:hide},rootPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'quicklink'                   => '{title_legend},name,headline,type;{nav_legend},pages,customLabel;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'booknav'                     => '{title_legend},name,headline,type;{nav_legend},showProtected,showHidden,rootPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'articlenav'                  => '{title_legend},name,headline,type;{config_legend},loadFirst;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'sitemap'                     => '{title_legend},name,headline,type;{nav_legend},showProtected,showHidden;{reference_legend:hide},rootPage;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'login'                       => '{title_legend},name,headline,type;{config_legend},autologin;{redirect_legend},jumpTo,redirectBack;{template_legend:hide},cols;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'logout'                      => '{title_legend},name,headline,type;{redirect_legend},jumpTo,redirectBack;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'personalData'                => '{title_legend},name,headline,type;{config_legend},editable;{redirect_legend},jumpTo;{template_legend:hide},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'registration'                => '{title_legend},name,headline,type;{config_legend},editable,newsletters,disableCaptcha;{account_legend},reg_groups,reg_allowLogin,reg_assignDir;{redirect_legend},jumpTo;{email_legend:hide},reg_activate;{template_legend:hide},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'lostPassword'                => '{title_legend},name,headline,type;{config_legend},reg_skipName,disableCaptcha;{redirect_legend},jumpTo;{email_legend:hide},reg_jumpTo,reg_password;{template_legend:hide},customTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'closeAccount'                => '{title_legend},name,headline,type;{config_legend},reg_close;{redirect_legend},jumpTo;{template_legend:hide},customTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'form'                        => '{title_legend},name,headline,type;{include_legend},form;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'search'                      => '{title_legend},name,headline,type;{config_legend},queryType,fuzzy,contextLength,totalLength,perPage,searchType;{redirect_legend:hide},jumpTo;{reference_legend:hide},rootPage;{template_legend:hide},searchTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'articleList'                 => '{title_legend},name,headline,type;{config_legend},skipFirst,inColumn;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'flash'                       => '{title_legend},name,headline,type;{config_legend},size,transparent,flashvars,altContent;{source_legend},source;{interact_legend:hide},interactive;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'randomImage'                 => '{title_legend},name,headline,type;{config_legend},imgSize,useCaption,fullsize;{source_legend},multiSRC;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space',
		'html'                        => '{title_legend},name,type;{html_legend},html;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests',
		'rss_reader'                  => '{title_legend},name,headline,type;{config_legend},rss_feed,numberOfItems,perPage,skipFirst,rss_cache;{template_legend:hide},rss_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space'
	],

	// Subpalettes
	'subpalettes' =>
	[
		'defineRoot'                  => 'rootPage',
		'source_internal'             => 'singleSRC',
		'source_external'             => 'url',
		'interactive'                 => 'flashID,flashJS',
		'protected'                   => 'groups',
		'reg_assignDir'               => 'reg_homeDir',
		'reg_activate'                => 'reg_jumpTo,reg_text'
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
			'relation'                => ['type'=>'belongsTo', 'load'=>'lazy']
		],
		'tstamp' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'name' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['name'],
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'headline' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['headline'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'inputUnit',
			'options'                 => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
			'eval'                    => ['maxlength'=>200, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'type' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['type'],
			'default'                 => 'navigation',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'filter'                  => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getModules'],
			'reference'               => &$GLOBALS['TL_LANG']['FMD'],
			'eval'                    => ['helpwizard'=>true, 'chosen'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'levelOffset' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['levelOffset'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'showLevel' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showLevel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>5, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'hardLimit' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['hardLimit'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'showProtected' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showProtected'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'defineRoot' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['defineRoot'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'rootPage' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['rootPage'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'foreignKey'              => 'tl_page.title',
			'eval'                    => ['fieldType'=>'radio', 'tl_class'=>'clr'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
		],
		'navigationTpl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['navigationTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getNavigationTemplates'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'customTpl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['customTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getModuleTemplates'],
			'eval'                    => ['includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'pages' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['pages'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'foreignKey'              => 'tl_page.title',
			'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox', 'files'=>true, 'orderField'=>'orderPages', 'mandatory'=>true],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'hasMany', 'load'=>'lazy']
		],
		'orderPages' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['orderSRC'],
			'sql'                     => "blob NULL"
		],
		'showHidden' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showHidden'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'customLabel' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['customLabel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>64, 'rgxp'=>'extnd', 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'autologin' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['autologin'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'jumpTo' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'foreignKey'              => 'tl_page.title',
			'eval'                    => ['fieldType'=>'radio'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'hasOne', 'load'=>'eager']
		],
		'redirectBack' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['redirectBack'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'cols' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['cols'],
			'default'                 => '2cl',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['1cl', '2cl'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'eval'                    => ['helpwizard'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'editable' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['editable'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'        => ['tl_module', 'getEditableMemberProperties'],
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL"
		],
		'memberTpl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['memberTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getMemberTemplates'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'tableless' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['tableless'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50 m12'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'form' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['form'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'foreignKey'              => 'tl_form.title',
			'options_callback'        => ['tl_module', 'getForms'],
			'eval'                    => ['chosen'=>true, 'tl_class'=>'w50'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
		],
		'queryType' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['queryType'],
			'default'                 => 'and',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['and', 'or'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'eval'                    => ['helpwizard'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'fuzzy' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['fuzzy'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50 m12'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'contextLength' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['contextLength'],
			'default'                 => 48,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'totalLength' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['totalLength'],
			'default'                 => 1000,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'perPage' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['perPage'],
			'default'                 => 0,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'searchType' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['searchType'],
			'default'                 => 'simple',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['simple', 'advanced'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'eval'                    => ['helpwizard'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'searchTpl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['searchTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getSearchTemplates'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'inColumn' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['inColumn'],
			'default'                 => 'main',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getLayoutSections'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'skipFirst' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['skipFirst'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'loadFirst' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['loadFirst'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'size' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['size'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'transparent' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['transparent'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50 m12'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'flashvars' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['flashvars'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['nospace'=>true, 'maxlength'=>255, 'tl_class'=>'long clr'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'altContent' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['altContent'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['mandatory'=>true, 'allowHtml'=>true, 'style'=>'height:60px', 'tl_class'=>'clr'],
			'sql'                     => "text NULL"
		],
		'source' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['source'],
			'default'                 => 'internal',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['internal', 'external'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'singleSRC' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['fieldType'=>'radio', 'filesOnly'=>true, 'mandatory'=>true, 'tl_class'=>'clr'],
			'sql'                     => "binary(16) NULL"
		],
		'url' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['url'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'interactive' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['interactive'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'flashID' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['flashID'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'extnd', 'nospace'=>true, 'unique'=>true, 'maxlength'=>64],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'flashJS' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['flashJS'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => ['class'=>'monospace', 'rte'=>'ace|js'],
			'sql'                     => "text NULL"
		],
		'imgSize' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['imgSize'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => $GLOBALS['TL_CROP'],
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => ['rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'useCaption' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['useCaption'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50 clr'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'fullsize' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['fullsize'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'multiSRC' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['multiSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderSRC', 'files'=>true, 'mandatory'=>true],
			'sql'                     => "blob NULL"
		],
		'orderSRC' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['orderSRC'],
			'sql'                     => "blob NULL"
		],
		'html' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['html'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['allowHtml'=>true, 'class'=>'monospace', 'rte'=>'ace|html', 'helpwizard'=>true],
			'explanation'             => 'insertTags',
			'sql'                     => "text NULL"
		],
		'rss_cache' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['rss_cache'],
			'default'                 => 3600,
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => [0, 5, 15, 30, 60, 300, 900, 1800, 3600, 10800, 21600, 43200, 86400],
			'eval'                    => ['tl_class'=>'w50'],
			'reference'               => &$GLOBALS['TL_LANG']['CACHE'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'rss_feed' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['rss_feed'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => ['mandatory'=>true, 'decodeEntities'=>true, 'style'=>'height:60px'],
			'sql'                     => "text NULL"
		],
		'rss_template' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['rss_template'],
			'default'                 => 'rss_default',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_module', 'getRssTemplates'],
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'numberOfItems' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['numberOfItems'],
			'default'                 => 3,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'disableCaptcha' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['disableCaptcha'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'reg_groups' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => ['multiple'=>true],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'hasMany', 'load'=>'lazy']
		],
		'reg_allowLogin' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_allowLogin'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'reg_skipName' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_skipName'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'reg_close' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_close'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['close_deactivate', 'close_delete'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_module'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'reg_assignDir' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_assignDir'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'reg_homeDir' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_homeDir'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['fieldType'=>'radio', 'tl_class'=>'clr'],
			'sql'                     => "binary(16) NULL"
		],
		'reg_activate' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_activate'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'reg_jumpTo' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'foreignKey'              => 'tl_page.title',
			'eval'                    => ['fieldType'=>'radio'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
		],
		'reg_text' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_text'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => ['style'=>'height:120px', 'decodeEntities'=>true, 'alwaysSave'=>true],
			'load_callback' =>
			[
				['tl_module', 'getActivationDefault']
			],
			'sql'                     => "text NULL"
		],
		'reg_password' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['reg_password'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => ['style'=>'height:120px', 'decodeEntities'=>true, 'alwaysSave'=>true],
			'load_callback'           =>
			[
				['tl_module', 'getPasswordDefault']
			],
			'sql'                     => "text NULL"
		],
		'protected' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'groups' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => ['mandatory'=>true, 'multiple'=>true],
			'sql'                     => "blob NULL",
			'relation'                => ['type'=>'hasMany', 'load'=>'lazy']
		],
		'guests' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['guests'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'cssID' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['cssID'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['multiple'=>true, 'size'=>2, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'space' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['space'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		]
	]
];


/**
 * Class tl_module
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_module extends Backend
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

		if (!$this->User->hasAccess('modules', 'themes'))
		{
			$this->log('Not enough permissions to access the modules module', __METHOD__, TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}
	}


	/**
	 * Return all front end modules as array
	 * @return array
	 */
	public function getModules()
	{
		$groups = [];

		foreach ($GLOBALS['FE_MOD'] as $k=>$v)
		{
			foreach (array_keys($v) as $kk)
			{
				$groups[$k][] = $kk;
			}
		}

		return $groups;
	}


	/**
	 * Return all editable fields of table tl_member
	 * @return array
	 */
	public function getEditableMemberProperties()
	{
		$return = [];

		System::loadLanguageFile('tl_member');
		$this->loadDataContainer('tl_member');

		foreach ($GLOBALS['TL_DCA']['tl_member']['fields'] as $k=>$v)
		{
			if ($v['eval']['feEditable'])
			{
				$return[$k] = $GLOBALS['TL_DCA']['tl_member']['fields'][$k]['label'][0];
			}
		}

		return $return;
	}


	/**
	 * Get all forms and return them as array
	 * @return array
	 */
	public function getForms()
	{
		if (!$this->User->isAdmin && !is_array($this->User->forms))
		{
			return [];
		}

		$arrForms = [];
		$objForms = $this->Database->execute("SELECT id, title FROM tl_form ORDER BY title");

		while ($objForms->next())
		{
			if ($this->User->hasAccess($objForms->id, 'forms'))
			{
				$arrForms[$objForms->id] = $objForms->title;
			}
		}

		return $arrForms;
	}


	/**
	 * Return all layout sections as array
	 * @return array
	 */
	public function getLayoutSections()
	{
		$arrCustom = [];
		$arrSections = ['header', 'left', 'right', 'main', 'footer'];

		// Check for custom layout sections
		$objLayout = $this->Database->query("SELECT sections FROM tl_layout WHERE sections!=''");

		while ($objLayout->next())
		{
			$arrCustom = array_merge($arrCustom, trimsplit(',', $objLayout->sections));
		}

		$arrCustom = array_unique($arrCustom);

		// Add the custom layout sections
		if (!empty($arrCustom) && is_array($arrCustom))
		{
			$arrSections = array_merge($arrSections, $arrCustom);
		}

		return $arrSections;
	}


	/**
	 * Return all navigation templates as array
	 * @return array
	 */
	public function getNavigationTemplates()
	{
		return $this->getTemplateGroup('nav_');
	}


	/**
	 * Return all module templates as array
	 * @return array
	 */
	public function getModuleTemplates()
	{
		return $this->getTemplateGroup('mod_');
	}


	/**
	 * Return all member templates as array
	 * @return array
	 */
	public function getMemberTemplates()
	{
		return $this->getTemplateGroup('member_');
	}


	/**
	 * Return all search templates as array
	 * @return array
	 */
	public function getSearchTemplates()
	{
		return $this->getTemplateGroup('search_');
	}


	/**
	 * Return all navigation templates as array
	 * @return array
	 */
	public function getRssTemplates()
	{
		return $this->getTemplateGroup('rss_');
	}


	/**
	 * Load the default activation text
	 * @param mixed
	 * @return mixed
	 */
	public function getActivationDefault($varValue)
	{
		if (!trim($varValue))
		{
			$varValue = (is_array($GLOBALS['TL_LANG']['tl_module']['emailText']) ? $GLOBALS['TL_LANG']['tl_module']['emailText'][1] : $GLOBALS['TL_LANG']['tl_module']['emailText']);
		}

		return $varValue;
	}


	/**
	 * Load the default password text
	 * @param mixed
	 * @return mixed
	 */
	public function getPasswordDefault($varValue)
	{
		if (!trim($varValue))
		{
			$varValue = (is_array($GLOBALS['TL_LANG']['tl_module']['passwordText']) ? $GLOBALS['TL_LANG']['tl_module']['passwordText'][1] : $GLOBALS['TL_LANG']['tl_module']['passwordText']);
		}

		return $varValue;
	}


	/**
	 * List a front end module
	 * @param array
	 * @return string
	 */
	public function listModule($row)
	{
		return '<div style="float:left">'. $row['name'] .' <span style="color:#b3b3b3;padding-left:3px">['. (isset($GLOBALS['TL_LANG']['FMD'][$row['type']][0]) ? $GLOBALS['TL_LANG']['FMD'][$row['type']][0] : $row['type']) .']</span>' . "</div>\n";
	}
}
