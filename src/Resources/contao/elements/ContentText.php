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
 * Front end content element "text".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentText extends \ContentElement
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_text';

	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		$this->text = \StringUtil::toHtml5($this->text);

		// Add the static files URL to images
		if (TL_FILES_URL != '')
		{
			$path = \Config::get('uploadPath') . '/';
			$this->text = str_replace(' src="' . $path, ' src="' . TL_FILES_URL . $path, $this->text);
		}

		$this->Template->text = \StringUtil::encodeEmail($this->text);
		$this->Template->addImage = false;

		// Add an image
		if ($this->addImage && $this->singleSRC != '')
		{
			$objModel = \FilesModel::findByUuid($this->singleSRC);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$this->singleSRC = $objModel->path;
				$this->addImageToTemplate($this->Template, $this->arrData, null, null, $objModel);
			}
		}
	}
}
