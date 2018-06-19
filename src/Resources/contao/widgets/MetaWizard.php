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
 * Provide methods to handle file meta information.
 *
 * @property array $metaFields
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class MetaWizard extends Widget
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
	 * Set an object property
	 *
	 * @param string $strKey   The property name
	 * @param mixed  $varValue The property value
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'metaFields':
				if (!array_is_assoc($varValue))
				{
					$varValue = array_combine($varValue, array_fill(0, \count($varValue), ''));
				}

				foreach($varValue as $strArrKey=>$varArrValue)
				{
					if (!\is_array($varArrValue))
					{
						$varValue[$strArrKey] = array('attributes'=>$varArrValue);
					}
				}

				$this->arrConfiguration['metaFields'] = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}

	/**
	 * Trim the values and add new languages if necessary
	 *
	 * @param mixed $varInput
	 *
	 * @return mixed
	 */
	public function validator($varInput)
	{
		if (!\is_array($varInput))
		{
			return null; // see #382
		}

		foreach ($varInput as $k=>$v)
		{
			if ($k != 'language')
			{
				$varInput[$k] = array_map('trim', $v);
			}
			else
			{
				if ($v != '')
				{
					// Take the fields from the DCA (see #4327)
					$varInput[$v] = array_combine(array_keys($this->metaFields), array_fill(0, \count($this->metaFields), ''));
				}

				unset($varInput[$k]);
			}
		}

		return $varInput;
	}

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$count = 0;
		$languages = $this->getLanguages();
		$return = '';
		$taken = array();

		$this->import('Database');
		$this->import('BackendUser', 'User');

		// Only show the root page languages (see #7112, #7667)
		$objRootLangs = $this->Database->query("SELECT REPLACE(language, '-', '_') AS language FROM tl_page WHERE type='root'");
		$existing = $objRootLangs->fetchEach('language');

		// Also add the existing keys (see #878)
		if (!empty($this->varValue))
		{
			$existing = array_unique(array_merge($existing, array_keys($this->varValue)));
		}

		$languages = array_intersect_key($languages, array_flip($existing));

		// Prefer languages matching the back end user's language (see #1358)
		uksort($languages, function ($a, $b)
		{
			if ($a == $this->User->language)
			{
				return -1;
			}

			if ($b == $this->User->language)
			{
				return 1;
			}

			if (strncmp($a, $this->User->language, 2) === 0)
			{
				return -1;
			}

			if (strncmp($b, $this->User->language, 2) === 0)
			{
				return 1;
			}

			return 0;
		});

		// Make sure there is at least an empty array
		if (empty($this->varValue) || !\is_array($this->varValue))
		{
			if (\count($languages) > 0)
			{
				$key = isset($languages[$GLOBALS['TL_LANGUAGE']]) ? $GLOBALS['TL_LANGUAGE'] : key($languages);
				$this->varValue = array($key=>array()); // see #4188
			}
			else
			{
				return '<p class="tl_info">' . $GLOBALS['TL_LANG']['MSC']['metaNoLanguages'] . '</p>';
			}
		}

		// Add the existing entries
		if (!empty($this->varValue))
		{
			$return = '<ul id="ctrl_' . $this->strId . '" class="tl_metawizard">';

			// Add the input fields
			foreach ($this->varValue as $lang=>$meta)
			{
				$return .= '
    <li class="' . (($count % 2 == 0) ? 'even' : 'odd') . '" data-language="' . $lang . '">';

				$return .= '<span class="lang">' . (isset($languages[$lang]) ? $languages[$lang] : $lang) . ' ' . \Image::getHtml('delete.svg', '', 'class="tl_metawizard_img" title="' . $GLOBALS['TL_LANG']['MSC']['delete'] . '" onclick="Backend.metaDelete(this)"') . '</span>';

				// Take the fields from the DCA (see #4327)
				foreach ($this->metaFields as $field=>$fieldConfig)
				{
					$return .= '<label for="ctrl_' . $field . '_' . $count . '">' . $GLOBALS['TL_LANG']['MSC']['aw_' . $field] . '</label> <input type="text" name="' . $this->strId . '[' . $lang . '][' . $field . ']" id="ctrl_' . $field . '_' . $count . '" class="tl_text" value="' . \StringUtil::specialchars($meta[$field]) . '"' . (!empty($fieldConfig['attributes']) ? ' ' . $fieldConfig['attributes'] : '') . '>';

					// DCA picker
					if (isset($fieldConfig['dcaPicker']) && (\is_array($fieldConfig['dcaPicker']) || $fieldConfig['dcaPicker'] === true))
					{
						$return .= \Backend::getDcaPickerWizard($fieldConfig['dcaPicker'], $this->strTable, $this->strField, $field . '_' . $count);
					}

					$return .= '<br>';
				}

				$return .= '
    </li>';

				$taken[] = $lang;
				++$count;
			}

			$return .= '
  </ul>';
		}

		$options = array('<option value="">-</option>');

		// Add the remaining languages
		foreach ($languages as $k=>$v)
		{
			$options[] = '<option value="' . $k . '"' . (\in_array($k, $taken) ? ' disabled' : '') . '>' . $v . '</option>';
		}

		$return .= '
  <div class="tl_metawizard_new">
    <select name="' . $this->strId . '[language]" class="tl_select tl_chosen" onchange="Backend.toggleAddLanguageButton(this)">' . implode('', $options) . '</select> <input type="button" class="tl_submit" disabled value="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['aw_new']) . '" onclick="Backend.metaWizard(this,\'ctrl_' . $this->strId . '\')">
  </div>';

		return $return;
	}
}

class_alias(MetaWizard::class, 'MetaWizard');
