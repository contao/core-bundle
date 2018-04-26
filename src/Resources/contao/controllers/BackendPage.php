<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Back end page picker.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BackendPage extends Backend
{

	/**
	 * Current Ajax object
	 * @var Ajax
	 */
	protected $objAjax;

	/**
	 * Initialize the controller
	 *
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Authenticate the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		if (!\System::getContainer()->get('security.authorization_checker')->isGranted('ROLE_USER'))
		{
			throw new AccessDeniedException('Access denied');
		}

		\System::loadLanguageFile('default');
	}

	/**
	 * Run the controller and parse the template
	 *
	 * @return Response
	 */
	public function run()
	{
		/** @var SessionInterface $objSession */
		$objSession = \System::getContainer()->get('session');

		/** @var BackendTemplate|object $objTemplate */
		$objTemplate = new \BackendTemplate('be_picker');
		$objTemplate->main = '';

		// Ajax request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax = new \Ajax(\Input::post('action'));
			$this->objAjax->executePreActions();
		}

		$strTable = \Input::get('table');
		$strField = \Input::get('field');

		// Define the current ID
		\define('CURRENT_ID', (\Input::get('table') ? $objSession->get('CURRENT_ID') : \Input::get('id')));

		$this->loadDataContainer($strTable);
		$strDriver = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
		$objDca = new $strDriver($strTable);
		$objDca->field = $strField;

		// Set the active record
		if ($this->Database->tableExists($strTable))
		{
			/** @var Model $strModel */
			$strModel = \Model::getClassFromTable($strTable);

			if (class_exists($strModel))
			{
				$objModel = $strModel::findByPk(\Input::get('id'));

				if ($objModel !== null)
				{
					$objDca->activeRecord = $objModel;
				}
			}
		}

		// AJAX request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax->executePostActions($objDca);
		}

		$objSession->set('filePickerRef', \Environment::get('request'));
		$arrValues = array_filter(explode(',', \Input::get('value')));

		// Call the load_callback
		if (\is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['load_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['load_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					$this->import($callback[0]);
					$arrValues = $this->{$callback[0]}->{$callback[1]}($arrValues, $objDca);
				}
				elseif (\is_callable($callback))
				{
					$arrValues = $callback($arrValues, $objDca);
				}
			}
		}

		/** @var PageSelector $strClass */
		$strClass = $GLOBALS['BE_FFL']['pageSelector'];

		/** @var PageSelector $objPageTree */
		$objPageTree = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$strTable]['fields'][$strField], $strField, $arrValues, $strField, $strTable, $objDca));

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = $objSession->getBag('contao_backend');

		$objTemplate->main = $objPageTree->generate();
		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']);
		$objTemplate->charset = \Config::get('characterSet');
		$objTemplate->addSearch = true;
		$objTemplate->search = $GLOBALS['TL_LANG']['MSC']['search'];
		$objTemplate->action = ampersand(\Environment::get('request'));
		$objTemplate->value = $objSessionBag->get('page_selector_search');
		$objTemplate->breadcrumb = $GLOBALS['TL_DCA']['tl_page']['list']['sorting']['breadcrumb'];

		if ($this->User->hasAccess('page', 'modules'))
		{
			$objTemplate->manager = $GLOBALS['TL_LANG']['MSC']['pageManager'];
			$objTemplate->managerHref = 'contao/main.php?do=page&amp;popup=1';
		}

		if (\Input::get('switch') && $this->User->hasAccess('files', 'modules'))
		{
			$objTemplate->switch = $GLOBALS['TL_LANG']['MSC']['filePicker'];
			$objTemplate->switchHref = str_replace('contao/page?', 'contao/file?', ampersand(\Environment::get('request')));
		}

		return $objTemplate->getResponse();
	}
}

class_alias(BackendPage::class, 'BackendPage');
