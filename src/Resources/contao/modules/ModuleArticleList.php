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
 * Front end module "article list".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleArticleList extends \Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_articlelist';

	/**
	 * Do not display the module if there are no articles
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			/** @var BackendTemplate|object $objTemplate */
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['articlelist'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$strBuffer = parent::generate();

		return !empty($this->Template->articles) ? $strBuffer : '';
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		if (!\strlen($this->inColumn))
		{
			$this->inColumn = 'main';
		}

		$id = $objPage->id;
		$objTarget = null;

		$this->Template->request = \Environment::get('request');

		// Show the articles of a different page
		if ($this->defineRoot && $this->rootPage > 0)
		{
			if (($objTarget = $this->objModel->getRelated('rootPage')) instanceof PageModel)
			{
				$id = $objTarget->id;

				/** @var PageModel $objTarget */
				$this->Template->request = $objTarget->getFrontendUrl();
			}
		}

		// Get published articles
		$objArticles = \ArticleModel::findPublishedByPidAndColumn($id, $this->inColumn);

		if ($objArticles === null)
		{
			$this->Template->articles = array();

			return;
		}

		$intCount = 0;
		$articles = array();
		$objHelper = $objTarget ?: $objPage; // PHP 5.6 compatibility (see #939)

		while ($objArticles->next())
		{
			// Skip first article
			if (++$intCount <= (int) $this->skipFirst)
			{
				continue;
			}

			$cssID = \StringUtil::deserialize($objArticles->cssID, true);

			$articles[] = array
			(
				'link' => $objArticles->title,
				'title' => \StringUtil::specialchars($objArticles->title),
				'id' => $cssID[0] ?: 'article-' . $objArticles->id,
				'articleId' => $objArticles->id,
				'href' => $objHelper->getFrontendUrl('/articles/' . ($objArticles->alias ?: $objArticles->id))
			);
		}

		$this->Template->articles = $articles;
	}
}
