<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\Exception\ResponseException;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Provide methods to handle table fields.
 *
 * @property integer $rows
 * @property integer $cols
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TableWizard extends \Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

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
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
		$arrColButtons = array('ccopy', 'cmovel', 'cmover', 'cdelete');
		$arrRowButtons = array('rcopy', 'rdelete', 'rdrag');

		// Make sure there is at least an empty array
		if (!\is_array($this->varValue) || empty($this->varValue))
		{
			$this->varValue = array(array(''));
		}

		// Begin the table
		$return = '<div id="tl_tablewizard">
  <table id="ctrl_'.$this->strId.'" class="tl_tablewizard">
  <thead>
    <tr>';

		// Add column buttons
		for ($i=0, $c=\count($this->varValue[0]); $i<$c; $i++)
		{
			$return .= '
      <td>';

			// Add column buttons
			foreach ($arrColButtons as $button)
			{
				$return .= ' <button type="button" data-command="' . $button . '" class="tl_tablewizard_img" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]) . '">' . \Image::getHtml(substr($button, 1).'.svg') . '</button>';
			}

			$return .= '</td>';
		}

		$return .= '
      <td></td>
    </tr>
  </thead>
  <tbody class="sortable">';

		// Add rows
		for ($i=0, $c=\count($this->varValue); $i<$c; $i++)
		{
			$return .= '
    <tr>';

			// Add cells
			for ($j=0, $d=\count($this->varValue[$i]); $j<$d; $j++)
			{
				$return .= '
      <td class="tcontainer"><textarea name="'.$this->strId.'['.$i.']['.$j.']" class="tl_textarea noresize" rows="'.$this->intRows.'" cols="'.$this->intCols.'"'.$this->getAttributes().'>'.\StringUtil::specialchars($this->varValue[$i][$j]).'</textarea></td>';
			}

			$return .= '
      <td>';

			// Add row buttons
			foreach ($arrRowButtons as $button)
			{
				if ($button == 'rdrag')
				{
					$return .= ' <button type="button" class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '" aria-hidden="true">' . \Image::getHtml('drag.svg') . '</button>';
				}
				else
				{
					$return .= ' <button type="button" data-command="' . $button . '" class="tl_tablewizard_img" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]) . '">' . \Image::getHtml(substr($button, 1).'.svg') . '</button>';
				}
			}

			$return .= '</td>
    </tr>';
		}

		$return .= '
  </tbody>
  </table>
  </div>
  <script>Backend.tableWizard("ctrl_'.$this->strId.'")</script>';

		return $return;
	}


	/**
	 * Return a form to choose a CSV file and import it
	 *
	 * @param DataContainer $dc
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @throws ResponseException
	 *
	 * @deprecated Deprecated since Contao 4.3 to be removed in 5.0.
	 *             Use the contao.controller.backend_csv_import service instead.
	 */
	public function importTable(DataContainer $dc)
	{
		$response = System::getContainer()->get('contao.controller.backend_csv_import')->importTableWizardAction($dc);

		if ($response instanceof RedirectResponse)
		{
			throw new ResponseException($response);
		}

		return $response->getContent();
	}
}
