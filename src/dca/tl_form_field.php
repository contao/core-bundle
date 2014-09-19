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
 * Table tl_form_field
 */
$GLOBALS['TL_DCA']['tl_form_field'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'ptable'                      => 'tl_form',
		'onload_callback' =>
		[
			['tl_form_field', 'checkPermission']
		],
		'sql' =>
		[
			'keys' =>
			[
				'id' => 'primary',
				'pid' => 'index'
			]
		]
	],

	// List
	'list' =>
	[
		'sorting' =>
		[
			'mode'                    => 4,
			'fields'                  => ['sorting'],
			'panelLayout'             => 'filter,search,limit',
			'headerFields'            => ['title', 'tstamp', 'formID', 'storeValues', 'sendViaEmail', 'recipient', 'subject', 'tableless'],
			'child_record_callback'   => ['tl_form_field', 'listFormFields']
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
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			],
			'cut' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"'
			],
			'delete' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			],
			'toggle' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
				'button_callback'     => ['tl_form_field', 'toggleIcon']
			],
			'show' =>
			[
				'label'               => &$GLOBALS['TL_LANG']['tl_form_field']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['type', 'fsType', 'multiple', 'storeFile', 'addSubmit', 'imageSubmit'],
		'default'                     => '{type_legend},type',
		'headline'                    => '{type_legend},type;{text_legend},text;{template_legend:hide},customTpl',
		'explanation'                 => '{type_legend},type;{text_legend},text;{template_legend:hide},customTpl',
		'fieldsetfsStart'             => '{type_legend},type;{fconfig_legend},fsType,label;{expert_legend:hide},class;{template_legend:hide},customTpl',
		'fieldsetfsStop'              => '{type_legend},type;{fconfig_legend},fsType;{template_legend:hide},customTpl',
		'html'                        => '{type_legend},type;{text_legend},html;{template_legend:hide},customTpl',
		'text'                        => '{type_legend},type,name,label;{fconfig_legend},mandatory,rgxp,placeholder;{expert_legend:hide},class,value,minlength,maxlength,accesskey,tabindex;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'password'                    => '{type_legend},type,name,label;{fconfig_legend},mandatory,rgxp,placeholder;{expert_legend:hide},class,value,minlength,maxlength,accesskey,tabindex;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'textarea'                    => '{type_legend},type,name,label;{fconfig_legend},mandatory,rgxp,placeholder;{size_legend},size;{expert_legend:hide},class,value,minlength,maxlength,accesskey,tabindex;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'select'                      => '{type_legend},type,name,label;{fconfig_legend},mandatory,multiple;{options_legend},options;{expert_legend:hide},class,accesskey,tabindex;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'radio'                       => '{type_legend},type,name,label;{fconfig_legend},mandatory;{options_legend},options;{expert_legend:hide},class;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'checkbox'                    => '{type_legend},type,name,label;{fconfig_legend},mandatory;{options_legend},options;{expert_legend:hide},class;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'upload'                      => '{type_legend},type,name,label;{fconfig_legend},mandatory,extensions,maxlength;{store_legend:hide},storeFile;{expert_legend:hide},class,accesskey,tabindex,fSize;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'hidden'                      => '{type_legend},type,name,value;{fconfig_legend},mandatory,rgxp;{template_legend:hide},customTpl',
		'captcha'                     => '{type_legend},type,label;{fconfig_legend},placeholder;{expert_legend:hide},class,accesskey,tabindex;{template_legend:hide},customTpl;{submit_legend},addSubmit',
		'submit'                      => '{type_legend},type,slabel;{image_legend:hide},imageSubmit;{expert_legend:hide},class,accesskey,tabindex;{template_legend:hide},customTpl'
	],

	// Subpalettes
	'subpalettes' =>
	[
		'multiple'                    => 'mSize',
		'storeFile'                   => 'uploadFolder,useHomeDir,doNotOverwrite',
		'addSubmit'                   => 'slabel',
		'imageSubmit'                 => 'singleSRC'
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
			'foreignKey'              => 'tl_form.title',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'relation'                => ['type'=>'belongsTo', 'load'=>'lazy']
		],
		'sorting' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'tstamp' =>
		[
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'invisible' =>
		[
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'type' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['type'],
			'default'                 => 'text',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_form_field', 'getFields'],
			'eval'                    => ['helpwizard'=>true, 'submitOnChange'=>true],
			'reference'               => &$GLOBALS['TL_LANG']['FFL'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'name' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'extnd', 'spaceToUnderscore'=>true, 'maxlength'=>64, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'label' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['label'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'text' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['text'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['rte'=>'tinyMCE', 'helpwizard'=>true],
			'explanation'             => 'insertTags',
			'sql'                     => "text NULL"
		],
		'html' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['html'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => ['mandatory'=>true, 'allowHtml'=>true, 'class'=>'monospace', 'rte'=>'ace|html'],
			'sql'                     => "text NULL"
		],
		'options' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['options'],
			'exclude'                 => true,
			'inputType'               => 'optionWizard',
			'eval'                    => ['mandatory'=>true, 'allowHtml'=>true],
			'sql'                     => "blob NULL"
		],
		'mandatory' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['mandatory'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'rgxp' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['rgxp'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => ['digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_form_field'],
			'eval'                    => ['helpwizard'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'placeholder' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['placeholder'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'minlength' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['minlength'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural', 'tl_class'=>'w50'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'maxlength' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['maxlength'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural', 'tl_class'=>'w50'],
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		],
		'size' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['size'],
			'default'                 => [4, 40],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'multiple'=>true, 'size'=>2, 'rgxp'=>'natural', 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'multiple' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['multiple'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true, 'tl_class'=>'clr'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'mSize' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['mSize'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'extensions' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['extensions'],
			'exclude'                 => true,
			'default'                 => 'jpg,jpeg,gif,png,pdf,doc,xls,ppt',
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'storeFile' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['storeFile'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'uploadFolder' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['uploadFolder'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['fieldType'=>'radio', 'tl_class'=>'clr'],
			'sql'                     => "binary(16) NULL"
		],
		'useHomeDir' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['useHomeDir'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'doNotOverwrite' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['doNotOverwrite'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'fsType' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['fsType'],
			'default'                 => 'fsStart',
			'exclude'                 => true,
			'inputType'               => 'radio',
			'options'                 => ['fsStart', 'fsStop'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_form_field'],
			'eval'                    => ['helpwizard'=>true, 'submitOnChange'=>true],
			'sql'                     => "varchar(32) NOT NULL default ''"
		],
		'class' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['class'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'value' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['value'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'accesskey' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['accesskey'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'alnum', 'maxlength'=>1, 'tl_class'=>'w50'],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'tabindex' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['tabindex'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'fSize' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['fSize'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural', 'tl_class'=>'w50'],
			'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
		],
		'customTpl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['customTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => ['tl_form_field', 'getFormFieldTemplates'],
			'eval'                    => ['includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'],
			'sql'                     => "varchar(64) NOT NULL default ''"
		],
		'addSubmit' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['addSubmit'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'slabel' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['slabel'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'maxlength'=>255],
			'sql'                     => "varchar(255) NOT NULL default ''"
		],
		'imageSubmit' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['imageSubmit'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true],
			'sql'                     => "char(1) NOT NULL default ''"
		],
		'singleSRC' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => ['fieldType'=>'radio', 'filesOnly'=>true, 'mandatory'=>true, 'tl_class'=>'clr'],
			'sql'                     => "binary(16) NULL"
		]
	]
];


/**
 * Class tl_form_field
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_form_field extends Backend
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
	 * Check permissions to edit table tl_form_field
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->forms) || empty($this->User->forms))
		{
			$root = [0];
		}
		else
		{
			$root = $this->User->forms;
		}

		$id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

		// Check current action
		switch (Input::get('act'))
		{
			case 'paste':
				// Allow
				break;

			case 'create':
			case 'select':
				if (!strlen(Input::get('id')) || !in_array(Input::get('id'), $root))
				{
					$this->log('Not enough permissions to access form ID "'.Input::get('id').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'cut':
			case 'copy':
				$pid = Input::get('pid');

				// Get form ID
				if (Input::get('mode') == 1)
				{
					$objField = $this->Database->prepare("SELECT pid FROM tl_form_field WHERE id=?")
											   ->limit(1)
											   ->execute(Input::get('pid'));

					if ($objField->numRows < 1)
					{
						$this->log('Invalid form field ID "'.Input::get('pid').'"', __METHOD__, TL_ERROR);
						$this->redirect('contao/main.php?act=error');
					}

					$pid = $objField->pid;
				}

				if (!in_array($pid, $root))
				{
					$this->log('Not enough permissions to '.Input::get('act').' form field ID "'.$id.'" to form ID "'.$pid.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				// NO BREAK STATEMENT HERE

			case 'edit':
			case 'show':
			case 'delete':
			case 'toggle':
				$objField = $this->Database->prepare("SELECT pid FROM tl_form_field WHERE id=?")
										   ->limit(1)
										   ->execute($id);

				if ($objField->numRows < 1)
				{
					$this->log('Invalid form field ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				if (!in_array($objField->pid, $root))
				{
					$this->log('Not enough permissions to '.Input::get('act').' form field ID "'.$id.'" of form ID "'.$objField->pid.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
			case 'overrideAll':
			case 'cutAll':
			case 'copyAll':
				if (!in_array($id, $root))
				{
					$this->log('Not enough permissions to access form ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				$objForm = $this->Database->prepare("SELECT id FROM tl_form_field WHERE pid=?")
										  ->execute($id);

				if ($objForm->numRows < 1)
				{
					$this->log('Invalid form ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}

				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objForm->fetchEach('id'));
				$this->Session->setData($session);
				break;

			default:
				if (strlen(Input::get('act')))
				{
					$this->log('Invalid command "'.Input::get('act').'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				elseif (!in_array($id, $root))
				{
					$this->log('Not enough permissions to access form ID "'.$id.'"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function listFormFields($arrRow)
	{
		$arrRow['required'] = $arrRow['mandatory'];
		$key = $arrRow['invisible'] ? 'unpublished' : 'published';

		$strType = '
<div class="cte_type ' . $key . '">' . $GLOBALS['TL_LANG']['FFL'][$arrRow['type']][0] . ($arrRow['name'] ? ' (' . $arrRow['name'] . ')' : '') . '</div>
<div class="limit_height' . (!Config::get('doNotCollapse') ? ' h32' : '') . '">';

		$strClass = $GLOBALS['TL_FFL'][$arrRow['type']];

		if (!class_exists($strClass))
		{
			return '';
		}

		$objWidget = new $strClass($arrRow);

		$strWidget = $objWidget->parse();
		$strWidget = preg_replace('/ name="[^"]+"/i', '', $strWidget);
		$strWidget = str_replace([' type="submit"', ' autofocus', ' required'], [' type="button"', '', ''], $strWidget);

		if ($objWidget instanceof Contao\FormHidden)
		{
			return $strType . "\n" . $objWidget->value . "\n</div>\n";
		}

		return $strType . '
<table class="tl_form_field_preview">
'.$strWidget.'</table>
</div>' . "\n";
	}


	/**
	 * Return a list of form fields
	 * @param Contao\DataContainer
	 * @return array
	 */
	public function getFields(Contao\DataContainer $dc)
	{
		$arrFields = $GLOBALS['TL_FFL'];
		$intPid = $dc->activeRecord->pid;

		if (Input::get('act') == 'overrideAll')
		{
			$intPid = Input::get('id');
		}

		$objForm = $this->Database->prepare("SELECT tableless FROM tl_form WHERE id=?")
								  ->limit(1)
								  ->execute($intPid);

		// Fieldsets are only supported in tableless forms
		if (!$objForm->tableless)
		{
			unset($arrFields['fieldset']);
		}

		// Add the translation
		foreach (array_keys($arrFields) as $key)
		{
			$arrFields[$key] = $GLOBALS['TL_LANG']['FFL'][$key][0];
		}

		return $arrFields;
	}


	/**
	 * Return all form field templates as array
	 * @return array
	 */
	public function getFormFieldTemplates()
	{
		return $this->getTemplateGroup('form_');
	}


	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.$row['invisible'];

		if ($row['invisible'])
		{
			$icon = 'invisible.gif';
		}

		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}


	/**
	 * Toggle the visibility of a form field
	 * @param int
	 * @param bool
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');
		$this->checkPermission();

		$objVersions = new Versions('tl_form_field', $intId);
		$objVersions->initialize();

		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA']['tl_form_field']['fields']['invisible']['save_callback']))
		{
			foreach ($GLOBALS['TL_DCA']['tl_form_field']['fields']['invisible']['save_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
				}
				elseif (is_callable($callback))
				{
					$blnVisible = $callback($blnVisible, $this);
				}
			}
		}

		// Update the database
		$this->Database->prepare("UPDATE tl_form_field SET tstamp=". time() .", invisible='" . ($blnVisible ? '' : 1) . "' WHERE id=?")
					   ->execute($intId);

		$objVersions->create();
		$this->log('A new version of record "tl_form_field.id='.$intId.'" has been created'.$this->getParentEntries('tl_form_field', $intId), __METHOD__, TL_GENERAL);
	}
}
