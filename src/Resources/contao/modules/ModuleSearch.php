<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Patchwork\Utf8;

/**
 * Front end module "search".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleSearch extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_search';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['search'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->pages = \StringUtil::deserialize($this->pages);

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		// Mark the x and y parameter as used (see #4277)
		if (isset($_GET['x']))
		{
			\Input::get('x');
			\Input::get('y');
		}

		// Trigger the search module from a custom form
		if (!isset($_GET['keywords']) && \Input::post('FORM_SUBMIT') == 'tl_search')
		{
			$_GET['keywords'] = \Input::post('keywords');
			$_GET['query_type'] = \Input::post('query_type');
			$_GET['per_page'] = \Input::post('per_page');
		}

		$blnFuzzy = $this->fuzzy;
		$strQueryType = \Input::get('query_type') ?: $this->queryType;

		$strKeywords = trim(\Input::get('keywords'));

		$this->Template->uniqueId = $this->id;
		$this->Template->queryType = $strQueryType;
		$this->Template->keyword = \StringUtil::specialchars($strKeywords);
		$this->Template->keywordLabel = $GLOBALS['TL_LANG']['MSC']['keywords'];
		$this->Template->optionsLabel = $GLOBALS['TL_LANG']['MSC']['options'];
		$this->Template->search = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['searchLabel']);
		$this->Template->matchAll = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['matchAll']);
		$this->Template->matchAny = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['matchAny']);
		$this->Template->action = ampersand(\Environment::get('indexFreeRequest'));
		$this->Template->advanced = ($this->searchType == 'advanced');

		// Redirect page
		if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel)
		{
			/** @var PageModel $objTarget */
			$this->Template->action = $objTarget->getFrontendUrl();
		}

		$this->Template->pagination = '';
		$this->Template->results = '';

		// Execute the search if there are keywords
		if ($strKeywords != '' && $strKeywords != '*' && !$this->jumpTo)
		{
			// Search pages
			if (!empty($this->pages) && \is_array($this->pages))
			{
				$varRootId = \implode('-', $this->pages);
				$arrPages = array();

				foreach ($this->pages as $intPageId)
				{
					$arrPages[] = $intPageId;
					$arrPages = \array_merge($arrPages, $this->Database->getChildRecords($intPageId, 'tl_page'));
				}

				$arrPages = \array_unique($arrPages);
			}
			// Website root
			else
			{
				/** @var PageModel $objPage */
				global $objPage;

				$varRootId = $objPage->rootId;
				$arrPages = $this->Database->getChildRecords($objPage->rootId, 'tl_page');
			}

			// HOOK: add custom logic (see #5223)
			if (isset($GLOBALS['TL_HOOKS']['customizeSearch']) && \is_array($GLOBALS['TL_HOOKS']['customizeSearch']))
			{
				foreach ($GLOBALS['TL_HOOKS']['customizeSearch'] as $callback)
				{
					$this->import($callback[0]);
					$this->{$callback[0]}->{$callback[1]}($arrPages, $strKeywords, $strQueryType, $blnFuzzy, $this);
				}
			}

			// Return if there are no pages
			if (empty($arrPages) || !\is_array($arrPages))
			{
				return;
			}

			$strCachePath = \StringUtil::stripRootDir(\System::getContainer()->getParameter('kernel.cache_dir'));

			$arrResult = null;
			$strChecksum = md5($strKeywords . $strQueryType . $varRootId . $blnFuzzy);
			$query_starttime = microtime(true);
			$strCacheFile = $strCachePath . '/contao/search/' . $strChecksum . '.json';

			// Load the cached result
			if (file_exists(\System::getContainer()->getParameter('kernel.project_dir') . '/' . $strCacheFile))
			{
				$objFile = new \File($strCacheFile);

				if ($objFile->mtime > time() - 1800)
				{
					$arrResult = json_decode($objFile->getContent(), true);
				}
				else
				{
					$objFile->delete();
				}
			}

			// Cache the result
			if ($arrResult === null)
			{
				try
				{
					$objSearch = \Search::searchFor($strKeywords, ($strQueryType == 'or'), $arrPages, 0, 0, $blnFuzzy);
					$arrResult = $objSearch->fetchAllAssoc();
				}
				catch (\Exception $e)
				{
					$this->log('Website search failed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
					$arrResult = array();
				}

				\File::putContent($strCacheFile, json_encode($arrResult));
			}

			$query_endtime = microtime(true);

			// Sort out protected pages
			if (\Config::get('indexProtected'))
			{
				$this->import('FrontendUser', 'User');

				foreach ($arrResult as $k=>$v)
				{
					if ($v['protected'])
					{
						if (!FE_USER_LOGGED_IN)
						{
							unset($arrResult[$k]);
						}
						else
						{
							$groups = \StringUtil::deserialize($v['groups']);

							if (empty($groups) || !\is_array($groups) || !\count(array_intersect($groups, $this->User->groups)))
							{
								unset($arrResult[$k]);
							}
						}
					}
				}

				$arrResult = array_values($arrResult);
			}

			$count = \count($arrResult);

			$this->Template->count = $count;
			$this->Template->page = null;
			$this->Template->keywords = $strKeywords;

			// No results
			if ($count < 1)
			{
				$this->Template->header = sprintf($GLOBALS['TL_LANG']['MSC']['sEmpty'], $strKeywords);
				$this->Template->duration = substr($query_endtime-$query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];

				return;
			}

			$from = 1;
			$to = $count;

			// Pagination
			if ($this->perPage > 0)
			{
				$id = 'page_s' . $this->id;
				$page = \Input::get($id) ?? 1;
				$per_page = \Input::get('per_page') ?: $this->perPage;

				// Do not index or cache the page if the page number is outside the range
				if ($page < 1 || $page > max(ceil($count/$per_page), 1))
				{
					throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
				}

				$from = (($page - 1) * $per_page) + 1;
				$to = (($from + $per_page) > $count) ? $count : ($from + $per_page - 1);

				// Pagination menu
				if ($to < $count || $from > 1)
				{
					$objPagination = new \Pagination($count, $per_page, \Config::get('maxPaginationLinks'), $id);
					$this->Template->pagination = $objPagination->generate("\n  ");
				}

				$this->Template->page = $page;
			}

			// Get the results
			for ($i=($from-1); $i<$to && $i<$count; $i++)
			{
				$objTemplate = new \FrontendTemplate($this->searchTpl);
				$objTemplate->setData($arrResult[$i]);
				$objTemplate->href = $arrResult[$i]['url'];
				$objTemplate->link = $arrResult[$i]['title'];
				$objTemplate->url = \StringUtil::specialchars(urldecode($arrResult[$i]['url']), true, true);
				$objTemplate->title = \StringUtil::specialchars(\StringUtil::stripInsertTags($arrResult[$i]['title']));
				$objTemplate->class = (($i == ($from - 1)) ? 'first ' : '') . (($i == ($to - 1) || $i == ($count - 1)) ? 'last ' : '') . (($i % 2 == 0) ? 'even' : 'odd');
				$objTemplate->relevance = sprintf($GLOBALS['TL_LANG']['MSC']['relevance'], number_format($arrResult[$i]['relevance'] / $arrResult[0]['relevance'] * 100, 2) . '%');

				$arrContext = array();
				$strText = \StringUtil::stripInsertTags($arrResult[$i]['text']);
				$arrMatches = \StringUtil::trimsplit(',', $arrResult[$i]['matches']);

				// Get the context
				foreach ($arrMatches as $strWord)
				{
					$arrChunks = array();
					preg_match_all('/(^|\b.{0,'.$this->contextLength.'}(?:\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}))' . preg_quote($strWord, '/') . '((?:\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}).{0,'.$this->contextLength.'}\b|$)/ui', $strText, $arrChunks);

					foreach ($arrChunks[0] as $strContext)
					{
						$arrContext[] = ' ' . $strContext . ' ';
					}
				}

				// Shorten the context and highlight all keywords
				if (!empty($arrContext))
				{
					$objTemplate->context = trim(\StringUtil::substrHtml(implode('…', $arrContext), $this->totalLength));
					$objTemplate->context = preg_replace('/(?<=^|\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan})(' . implode('|', array_map('preg_quote', $arrMatches)) . ')(?=\PL|\p{Hiragana}|\p{Katakana}|\p{Han}|\p{Myanmar}|\p{Khmer}|\p{Lao}|\p{Thai}|\p{Tibetan}|$)/ui', '<mark class="highlight">$1</mark>', $objTemplate->context);

					$objTemplate->hasContext = true;
				}

				$this->Template->results .= $objTemplate->parse();
			}

			$this->Template->header = vsprintf($GLOBALS['TL_LANG']['MSC']['sResults'], array($from, $to, $count, $strKeywords));
			$this->Template->duration = substr($query_endtime-$query_starttime, 0, 6) . ' ' . $GLOBALS['TL_LANG']['MSC']['seconds'];
		}
	}
}

class_alias(ModuleSearch::class, 'ModuleSearch');
