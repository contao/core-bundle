<?php

/**
 * Contao Open Source CMS
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Class FormFieldsetStart
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FormFieldsetStart extends Widget
{

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_fieldsetStart';

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
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->title = $this->label;

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
		return sprintf('<fieldset%s>%s',
						($this->strClass ? ' class="' . $this->strClass . '"' : ''),
						($this->label ? '<legend>' . $this->label . '</legend>' : ''));
	}
}

class_alias(FormFieldsetStart::class, 'FormFieldsetStart');
