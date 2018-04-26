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

/**
 * Back end alerts wizard.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BackendAlerts extends Backend
{

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
		/** @var BackendTemplate|object $objTemplate */
		$objTemplate = new \BackendTemplate('be_alerts');
		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['systemMessages']);
		$objTemplate->charset = \Config::get('characterSet');
		$objTemplate->messages = \Message::generateUnwrapped() . \Backend::getSystemMessages();
		$objTemplate->noMessages = $GLOBALS['TL_LANG']['MSC']['noSystemMessages'];

		return $objTemplate->getResponse();
	}
}

class_alias(BackendAlerts::class, 'BackendAlerts');
