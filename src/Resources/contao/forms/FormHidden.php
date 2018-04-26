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
 * Class FormHidden
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FormHidden extends Widget
{

	/**
	 * Submit user input
	 *
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'form_hidden';

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string The widget markup
	 */
	public function generate()
	{
		return sprintf('<input type="hidden" name="%s" value="%s"%s',
						$this->strName,
						\StringUtil::specialchars($this->varValue),
						$this->strTagEnding);
	}
}

class_alias(FormHidden::class, 'FormHidden');
