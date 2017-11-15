<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


/**
 * Provide methods to handle input field "picker".
 *
 * @property boolean        $multiple
 * @property array          $rootNodes
 * @property string         $fieldType
 * @property string         $foreignTable
 * @property string         $context
 * @property string         $labelField
 * @property string         $icon
 * @property boolean        $sortable
 * @property array|callable $label_callback
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class PickerWidget extends \Widget
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
	 * Load the database object
	 *
	 * @param array $arrAttributes
	 */
	public function __construct($arrAttributes=null)
	{
		$this->import('Database');
		parent::__construct($arrAttributes);
	}


	/**
	 * Return an array if the "multiple" attribute is set
	 *
	 * @param mixed $varInput
	 *
	 * @return mixed
	 */
	protected function validator($varInput)
	{
		$this->checkValue($varInput);

		if ($this->hasErrors())
		{
			return '';
		}

		// Return the value as usual
		if ($varInput == '')
		{
			if ($this->mandatory)
			{
				$this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
			}

			return '';
		}
		elseif (strpos($varInput, ',') === false)
		{
			return $this->multiple ? array(intval($varInput)) : intval($varInput);
		}
		else
		{
			$arrValue = array_map('intval', array_filter(explode(',', $varInput)));

			return $this->multiple ? $arrValue : $arrValue[0];
		}
	}


	/**
	 * Check the selected value
	 *
	 * @param mixed $varInput
	 */
	protected function checkValue($varInput)
	{
		if ($varInput == '' || !is_array($this->rootNodes) || !$this->Database->fieldExists('pid', $this->foreignTable))
		{
			return;
		}

		if (strpos($varInput, ',') === false)
		{
			$arrIds = array(intval($varInput));
		}
		else
		{
			$arrIds = array_map('intval', array_filter(explode(',', $varInput)));
		}

		if (count(array_diff($arrIds, array_merge($this->rootNodes, $this->Database->getChildRecords($this->rootNodes, $this->foreignTable)))) > 0)
		{
			$this->addError($GLOBALS['TL_LANG']['ERR']['invalidRecords']);
		}
	}


	/**
	 * Generate the record label
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	protected function generateRecordLabel(array $data)
	{
		// Label callback
		if (is_array($this->label_callback))
		{
			$callback = $this->label_callback;

			$label = static::importStatic($callback[0])->{$callback[1]}($data, $this->objDca);
		}
		elseif (is_callable($callback = $this->label_callback))
		{
			$label = $callback($data, $this->objDca);
		}
		elseif ($this->labelField)
		{
			// Label field
			$label = $data[$this->labelField];
		}
		else
		{
			$label = $data['id'];
		}

		return $label;
	}


	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$arrValues = array();

		// Generate the records
		if (!empty($this->varValue)) // Can be an array
		{
			$ids = array_map('intval', (array) $this->varValue);
			$records = $this->Database->execute("SELECT * FROM {$this->foreignTable} WHERE id IN (" . implode(',', $ids) . ")" . ($this->sortable ? (" ORDER BY " . $this->Database->findInSet('id', $ids)) : ""));

			while ($records->next())
			{
				$arrValues[$records->id] = $this->generateRecordLabel($records->row());
			}
		}

		$return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.implode(',', array_keys($arrValues)).'">
  <div class="selector_container">' . (($this->sortable && count($arrValues) > 1) ? '
    <p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>' : '') . '
    <ul id="sort_'.$this->strId.'" class="'.($this->sortable ? 'sortable' : '').'">';

		foreach ($arrValues as $k=>$v)
		{
			$return .= '<li data-id="'.$k.'">'.$v.'</li>';
		}

		$return .= '</ul>';

		if (!\System::getContainer()->get('contao.picker.builder')->supportsContext($this->context))
		{
			$return .= '
	<p><button class="tl_submit" disabled>'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</button></p>';
		}
		else
		{
			$extras = [
				'fieldType' => $this->fieldType ?: 'radio',
				'source' => $this->strTable.'.'.$this->currentRecord,
			];

			if (is_array($this->rootNodes))
			{
				$extras['rootNodes'] = array_values($this->rootNodes);
			}

			$return .= '
	<p><a href="' . ampersand(\System::getContainer()->get('contao.picker.builder')->getUrl($this->context, $extras)) . '" class="tl_submit" id="picker_' . $this->strName . '">'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</a></p>
	<script>
	  $("picker_' . $this->strName . '").addEvent("click", function(e) {
		e.preventDefault();
		Backend.openModalSelector({
		  "id": "tl_listing",
		  "title": "' . \StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0])) . '",
		  "url": this.href + document.getElementById("ctrl_'.$this->strId.'").value,
		  "callback": function(table, value) {
			new Request.Contao({
			  evalScripts: false,
			  onSuccess: function(txt, json) {
				$("ctrl_' . $this->strId . '").getParent("div").set("html", json.content);
				json.javascript && Browser.exec(json.javascript);
			  }
			}).post({"action":"reloadPicker", "name":"' . $this->strId . '", "value":value.join("\t"), "REQUEST_TOKEN":"' . REQUEST_TOKEN . '"});
		  }
		});
	  });
	</script>' . ($this->sortable ? '
	<script>Backend.makeMultiSrcSortable("sort_'.$this->strId.'", "ctrl_'.$this->strId.'", "ctrl_'.$this->strId.'")</script>' : '');
		}

		$return = '<div>' . $return . '</div></div>';

		return $return;
	}
}
