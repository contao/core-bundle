<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\DataContainer\DcaFilterInterface;


/**
 * Provide methods to handle textareas.
 *
 * @property integer $maxlength
 * @property boolean $mandatory
 * @property boolean $rte
 * @property integer $rows
 * @property integer $cols
 * @property string  $dcaPicker
 * @property string  $fieldType
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TextArea extends \Widget implements DcaFilterInterface
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Add a for attribute
	 * @var boolean
	 */
	protected $blnForAttribute = true;

	/**
	 * Rows
	 * @var integer
	 */
	protected $intRows = 12;

	/**
	 * Columns
	 * @var integer
	 */
	protected $intCols = 80;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * Add specific attributes
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'maxlength':
				if ($varValue > 0)
				{
					$this->arrAttributes['maxlength'] = $varValue;
				}
				break;

			case 'mandatory':
				if ($varValue)
				{
					$this->arrAttributes['required'] = 'required';
				}
				else
				{
					unset($this->arrAttributes['required']);
				}
				parent::__set($strKey, $varValue);
				break;

			case 'rows':
				$this->intRows = $varValue;
				break;

			case 'cols':
				$this->intCols = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDcaFilter()
	{
		if (!$this->dcaPicker)
		{
			return array();
		}

		$arrFilters = array();

		if ($this->fieldType)
		{
			$arrFilters['fieldType'] = $this->fieldType;
		}

		$do = \Input::get('do');

		if ($do === null || !isset($this->dcaPicker[$do]))
		{
			return $arrFilters;
		}

		$arrConfig = $this->dcaPicker[$do];

		// Show files in file tree
		if (isset($arrConfig['files']) && $arrConfig['files'] === true)
		{
			$arrFilters['files'] = true;
		}

		// Only files can be selected
		if (isset($arrConfig['filesOnly']) && $arrConfig['filesOnly'] === true)
		{
			$arrFilters['filesOnly'] = true;
		}

		// Only files within a custom path can be selected
		if (!empty($arrConfig['path']))
		{
			$arrFilters['root'] = array($arrConfig['path']);
		}

		// Only certain file types can be selected
		if (!empty($arrConfig['extensions']))
		{
			$arrFilters['extensions'] = $arrConfig['path'];
		}

		// Predefined node set (see #3563)
		if (isset($arrConfig['rootNodes']) && is_array($arrConfig['rootNodes']))
		{
			// Allow only those roots that are allowed in root nodes
			if (!empty($GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root']))
			{
				$root = array_intersect(array_merge($arrConfig['rootNodes'], $this->Database->getChildRecords($arrConfig['rootNodes'], 'tl_page')), $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root']);

				if (empty($root))
				{
					$root = $arrConfig['rootNodes'];
					$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['breadcrumb'] = ''; // hide the breadcrumb menu
				}

				$arrFilters['root'] = $this->eliminateNestedPages($root);
			}
			else
			{
				$arrFilters['root'] = $this->eliminateNestedPages($arrConfig['rootNodes']);
			}
		}

		return $arrFilters;
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		if ($this->rte)
		{
			$this->strClass = trim($this->strClass . ' noresize');
		}

		return sprintf('<textarea name="%s" id="ctrl_%s" class="tl_textarea%s" rows="%s" cols="%s"%s onfocus="Backend.getScrollOffset()">%s</textarea>%s',
						$this->strName,
						$this->strId,
						(($this->strClass != '') ? ' ' . $this->strClass : ''),
						$this->intRows,
						$this->intCols,
						$this->getAttributes(),
						\StringUtil::specialchars($this->varValue),
						$this->wizard);
	}
}
