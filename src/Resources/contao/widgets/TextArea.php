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
 * @property boolean $files
 * @property boolean $filesOnly
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

		// Show files in file tree
		if ($this->files)
		{
			$arrFilters['files'] = true;
		}

		// Only files can be selected
		if ($this->filesOnly)
		{
			$arrFilters['filesOnly'] = true;
		}

		if ($this->fieldType)
		{
			$arrFilters['fieldType'] = $this->fieldType;
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
