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
 * Front end content element "hyperlink".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentHyperlink extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_hyperlink';

	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		if (substr($this->url, 0, 7) == 'mailto:')
		{
			$this->url = \StringUtil::encodeEmail($this->url);
		}
		else
		{
			$this->url = ampersand($this->url);
		}

		$embed = explode('%s', $this->embed);

		// Use an image instead of the title
		if ($this->useImage && $this->singleSRC != '')
		{
			$objModel = \FilesModel::findByUuid($this->singleSRC);

			if ($objModel !== null && is_file(TL_ROOT . '/' . $objModel->path))
			{
				$this->singleSRC = $objModel->path;
				$this->addImageToTemplate($this->Template, $this->arrData, null, null, $objModel);
				$this->Template->useImage = true;
			}
		}

		if (strncmp($this->rel, 'lightbox', 8) !== 0)
		{
			$this->Template->attribute = ' rel="'. $this->rel .'"';
		}
		else
		{
			$this->Template->attribute = ' data-lightbox="'. substr($this->rel, 9, -1) .'"';
		}

		// Deprecated since Contao 4.0, to be removed in Contao 5.0
		$this->Template->rel = $this->rel;

		if ($this->linkTitle == '')
		{
			$this->linkTitle = $this->url;
		}

		$this->Template->href = $this->url;
		$this->Template->embed_pre = $embed[0];
		$this->Template->embed_post = $embed[1];
		$this->Template->link = $this->linkTitle;
		$this->Template->target = '';

		if ($this->titleText)
		{
			$this->Template->linkTitle = \StringUtil::specialchars($this->titleText);
		}

		// Override the link target
		if ($this->target)
		{
			$this->Template->target = ' target="_blank"';
		}

		// Unset the title attributes in the back end (see #6258)
		if (TL_MODE == 'BE')
		{
			$this->Template->title = '';
			$this->Template->linkTitle = '';
		}
	}
}

class_alias(ContentHyperlink::class, 'ContentHyperlink');
