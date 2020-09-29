<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Patchwork\Utf8;

/**
 * Class FormFieldset
 *
 * @property string $fsType
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FormFieldset extends \Widget
{
	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_fieldset';

	/**
	 * Do not validate
	 */
	public function validate()
	{
	}

	/**
	 * Parse the template file and return it as string
	 *
	 * @param array $arrAttributes An optional attributes array
	 *
	 * @return string The template markup
	 */
	public function parse($arrAttributes=null)
	{
		// Return a wildcard in the back end
		if (TL_MODE == 'BE')
		{
			/** @var BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_wildcard');

			if ($this->fsType == 'fsStart')
			{
				$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['tl_form_field']['fsStart'][0]) . ' ###' . ($this->label ? '<br>' . $this->label : '');
			}
			else
			{
				$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['tl_form_field']['fsStop'][0]) . ' ###';
			}

			return $objTemplate->parse();
		}

		return parent::parse($arrAttributes);
	}

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string The widget markup
	 */
	public function generate()
	{
		if ($this->fsType == 'fsStart')
		{
			return "  <fieldset" . ($this->strClass ? ' class="' . $this->strClass . '"' : '') . ">\n" . (($this->label != '') ? "  <legend>" . $this->label . "</legend>\n" : '');
		}

		return "  </fieldset>\n";
	}
}
