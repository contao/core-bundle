<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\Security\Exception\LockedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


/**
 * Handle back end logins and logouts.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BackendIndex extends \Backend
{

	/**
	 * Initialize the controller
	 *
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Login the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		\System::loadLanguageFile('default');
		\System::loadLanguageFile('tl_user');
	}


	/**
	 * Run the controller and parse the login template
	 *
	 * @return Response
	 */
	public function run()
	{
		/** @var BackendTemplate|object $objTemplate */
		$objTemplate = new \BackendTemplate('be_login');

		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->messages = $this->getLoginMessages();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->languages = \System::getLanguages(true); // backwards compatibility
		$objTemplate->title = \StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['loginTo'], \Config::get('websiteTitle')));
		$objTemplate->charset = \Config::get('characterSet');
		$objTemplate->action = ampersand(\Environment::get('request'));
		$objTemplate->userLanguage = $GLOBALS['TL_LANG']['tl_user']['language'][0];
		$objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['loginBT'];
		$objTemplate->curLanguage = \Input::post('language') ?: str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);
		$objTemplate->curUsername = \Input::post('username') ?: '';
		$objTemplate->loginButton = \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['continue']);
		$objTemplate->username = $GLOBALS['TL_LANG']['tl_user']['username'][0];
		$objTemplate->password = $GLOBALS['TL_LANG']['MSC']['password'][0];
		$objTemplate->feLink = $GLOBALS['TL_LANG']['MSC']['feLink'];
		$objTemplate->default = $GLOBALS['TL_LANG']['MSC']['default'];
		$objTemplate->jsDisabled = $GLOBALS['TL_LANG']['MSC']['jsDisabled'];

		return $objTemplate->getResponse();
	}

	/**
	 * Gets the security messages for the login view.
	 *
	 * @return string
	 */
	private function getLoginMessages()
	{
		$strMessages = \Message::generateUnwrapped();

		$exception = \System::getContainer()->get('security.authentication_utils')->getLastAuthenticationError();

		if ($exception instanceof LockedException)
		{
			$strMessages .= '<p class="tl_error">' . sprintf($GLOBALS['TL_LANG']['ERR']['accountLocked'], $exception->getLockedMinutes()) . '</p>';
		}
		else if ($exception instanceof AuthenticationException)
		{
			$strMessages .= '<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['invalidLogin'] . '</p>';
		}

		if (!empty($strMessages))
		{
			$strMessages = '<div class="tl_message">' . $strMessages . '</div>';
		}

		return $strMessages;
	}
}
