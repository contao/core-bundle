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
 * Front end module "navigation".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleNavigation extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_navigation';

	/**
	 * Do not display the module if there are no menu items
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['navigation'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$strBuffer = parent::generate();

		return ($this->Template->items != '') ? $strBuffer : '';
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Set the trail and level
		if ($this->defineRoot && $this->rootPage > 0)
		{
			$trail = array($this->rootPage);
			$level = 0;
		}
		else
		{
			$trail = $objPage->trail;
			$level = ($this->levelOffset > 0) ? $this->levelOffset : 0;
		}

		$lang = null;
		$host = null;

		// Overwrite the domain and language if the reference page belongs to a differnt root page (see #3765)
		if ($this->defineRoot && $this->rootPage > 0)
		{
			$objRootPage = \PageModel::findWithDetails($this->rootPage);

			// Set the language
			if (\Config::get('addLanguageToUrl') && $objRootPage->rootLanguage != $objPage->rootLanguage)
			{
				$lang = $objRootPage->rootLanguage;
			}

			// Set the domain
			if ($objRootPage->rootId != $objPage->rootId && $objRootPage->domain != '' && $objRootPage->domain != $objPage->domain)
			{
				$host = $objRootPage->domain;
			}
		}

		$this->Template->request = ampersand(\Environment::get('indexFreeRequest'));
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->skipNavigation = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
		$this->Template->items = $this->renderNavigation($trail[$level], 1, $host, $lang);
	}
}

class_alias(ModuleNavigation::class, 'ModuleNavigation');
