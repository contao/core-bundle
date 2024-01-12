<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Provide methods to handle text fields with interval drop down menu.
 *
 * @property integer $maxlength
 * @property array   $options
 * @property boolean $mandatory
 */
class TimePeriod extends Widget
{
	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

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

			case 'options':
				$this->arrOptions = StringUtil::deserialize($varValue);
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}

	/**
	 * @param mixed $varInput
	 *
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		foreach ($varInput as $k=>$v)
		{
			if ($k == 'unit')
			{
				if (!$this->isValidOption($v))
				{
					$varInput[$k] = '';
					$this->addError($GLOBALS['TL_LANG']['ERR']['invalid']);
				}
			}
			else
			{
				$varInput[$k] = parent::validator($v);
			}
		}

		return $varInput;
	}

	/**
	 * Only check against the unit values (see #7246)
	 *
	 * @param array $arrOption The options array
	 *
	 * @return string The "selected" attribute or an empty string
	 */
	protected function isSelected($arrOption)
	{
		if (empty($this->varValue) && !Input::isPost() && ($arrOption['default'] ?? null))
		{
			return $this->optionSelected(1, 1);
		}

		if (empty($this->varValue) || !\is_array($this->varValue))
		{
			return '';
		}

		return $this->optionSelected($arrOption['value'] ?? null, $this->varValue['unit'] ?? null);
	}

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$arrUnits = array();

		// Add an empty option if there are none (see #5067)
		if (empty($this->arrOptions))
		{
			$this->arrOptions = array(array('value'=>'', 'label'=>'-'));
		}

		foreach ($this->arrOptions as $arrUnit)
		{
			$arrUnits[] = sprintf(
				'<option value="%s"%s>%s</option>',
				self::specialcharsValue($arrUnit['value'] ?? ''),
				$this->isSelected($arrUnit),
				$arrUnit['label'] ?? '-'
			);
		}

		if (!\is_array($this->varValue))
		{
			$this->varValue = array('value'=>$this->varValue);
		}

		return sprintf(
			'<input type="text" name="%s[value]" id="ctrl_%s" class="tl_text_interval%s" value="%s"%s data-action="focus->contao--scroll-offset#store"> <select name="%s[unit]" class="tl_select_interval" data-action="focus->contao--scroll-offset#store"%s>%s</select>%s',
			$this->strName,
			$this->strId,
			$this->strClass ? ' ' . $this->strClass : '',
			self::specialcharsValue($this->varValue['value'] ?? ''),
			$this->getAttributes(),
			$this->strName,
			$this->getAttribute('disabled'),
			implode('', $arrUnits),
			$this->wizard
		);
	}
}
