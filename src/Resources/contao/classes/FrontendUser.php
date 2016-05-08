<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Contao\CoreBundle\Security\Authentication\ContaoToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;


/**
 * Provide methods to manage front end users.
 *
 * @property array   $allGroups
 * @property string  $loginPage
 * @property boolean $blnRecordExists
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FrontendUser extends \User
{

	/**
	 * Current object instance (do not remove)
	 * @var FrontendUser
	 */
	protected static $objInstance;

	/**
	 * Name of the corresponding table
	 * @var string
	 */
	protected $strTable = 'tl_member';

	/**
	 * Name of the current cookie
	 * @var string
	 */
	protected $strCookie = 'FE_USER_AUTH';

	/**
	 * Group login page
	 * @var string
	 */
	protected $strLoginPage;

	/**
	 * Groups
	 * @var array
	 */
	protected $arrGroups;


	/**
	 * Initialize the object
	 */
	protected function __construct()
	{
		parent::__construct();

		$this->strIp = \Environment::get('ip');
		$this->strHash = \Input::cookie($this->strCookie);
	}


	/**
	 * Extend parent setter class and modify some parameters
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'allGroups':
				$this->arrGroups = $varValue;
				break;

			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Extend parent getter class and modify some parameters
	 *
	 * @param string $strKey
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'allGroups':
				return $this->arrGroups;
				break;

			case 'loginPage':
				return $this->strLoginPage;
				break;
		}

		return parent::__get($strKey);
	}


	/**
	 * Authenticate a user
	 *
	 * @return boolean
	 */
	public function authenticate()
	{
		// Default authentication
		if (parent::authenticate())
		{
			return true;
		}

		// Check whether auto login is enabled
		if (\Config::get('autologin') > 0)
		{

			$isAuthenticated = false;

			// Try to log in the user by the new multi auto login implementation
			$rememberMeService = $this->getContainer()->get('contao.security.remember_me');
			$rememberMeToken = $rememberMeService->autoLogin($this->getContainer()->get('request_stack')->getCurrentRequest());
			if($rememberMeToken instanceof RememberMeToken) {
				$isAuthenticated = $rememberMeToken->isAuthenticated();
			}

			// Try to find the user by his auto login cookie
			if (($strCookie = \Input::cookie('FE_AUTO_LOGIN')) != '' && $this->findBy('autologin', $strCookie) !== false)
			{
				// Check the auto login period
				if ($this->createdOn >= (time() - \Config::get('autologin')))
				{
					$isAuthenticated = true;
				}
			}

			// If the user could be authenticated, validate the account status
			if ($isAuthenticated === true && $this->checkAccountStatus() !== false)
			{
				$this->setUserFromDb();

				// Last login date
				$this->lastLogin = $this->currentLogin;
				$this->currentLogin = time();
				$this->save();

				// Generate the session
				$this->generateSession();
				$this->log('User "' . $this->username . '" was logged in automatically', __METHOD__, TL_ACCESS);

				// Reload the page
				\Controller::reload();

				return true;
			}

			// Remove the cookie if it is invalid to enable loading cached pages
			$this->setCookie('FE_AUTO_LOGIN', $strCookie, (time() - 86400), null, null, false, true);
		}

		return false;
	}


	/**
	 * Add the auto login resources
	 *
	 * @return boolean
	 */
	public function login()
	{
		// Default routine
		if (parent::login() == false)
		{
			return false;
		}

		// Set the auto login data
		if (\Config::get('autologin') > 0 && \Input::post('autologin'))
		{

			$time = time();
			$strToken = md5(uniqid(mt_rand(), true));

			$this->createdOn = $time;
			$this->autologin = $strToken;
			$this->save();

			// New Autologin
			$response = new Response();
			$userToken = new ContaoToken($this); // Create the user token since only an anon Token exists so far
			$this->getContainer()->get('contao.security.remember_me')
				->loginSuccess(
					$this->getContainer()->get('request_stack')->getCurrentRequest(),
					$response,
					$userToken
				);
			$response->sendHeaders();

			$this->setCookie('FE_AUTO_LOGIN', $strToken, ($time + \Config::get('autologin')), null, null, false, true);
		}

		return true;
	}


	/**
	 * Remove the auto login resources
	 *
	 * @return boolean
	 */
	public function logout()
	{
		// $this->getContainer()->get('security.token_storage')->getToken() gets unset from service in User::logout implementation
		// so we grab a copy here cause we need it for unsetting the remember me cookie
		$userToken = $this->getContainer()->get('security.token_storage')->getToken();

		// Default routine
		if (parent::logout() == false)
		{
			return false;
		}

		// Reset the auto login data
		if ($this->blnRecordExists)
		{
			$this->autologin = null;
			$this->createdOn = 0;
			$this->save();
		}

		// Remove the auto login cookie
		$this->setCookie('FE_AUTO_LOGIN', $this->autologin, (time() - 86400), null, null, false, true);

		$this->getContainer()->get('contao.security.remember_me')->logout(
			$this->getContainer()->get('request_stack')->getCurrentRequest(),
			new Response(),
			$userToken
		);

		return true;
	}


	/**
	 * Save the original group membership
	 *
	 * @param string $strColumn
	 * @param mixed  $varValue
	 *
	 * @return boolean
	 */
	public function findBy($strColumn, $varValue)
	{
		if (parent::findBy($strColumn, $varValue) === false)
		{
			return false;
		}

		$this->arrGroups = $this->groups;

		return true;
	}


	/**
	 * Restore the original group membership
	 */
	public function save()
	{
		$groups = $this->groups;
		$this->arrData['groups'] = $this->arrGroups;
		parent::save();
		$this->groups = $groups;
	}


	/**
	 * Set all user properties from a database record
	 */
	protected function setUserFromDb()
	{
		$this->intId = $this->id;

		// Unserialize values
		foreach ($this->arrData as $k=>$v)
		{
			if (!is_numeric($v))
			{
				$this->$k = \StringUtil::deserialize($v);
			}
		}

		// Set language
		if ($this->language != '')
		{
			\System::getContainer()->get('request_stack')->getCurrentRequest()->setLocale($this->language);
			\System::getContainer()->get('translator')->setLocale($this->language);

			// Deprecated since Contao 4.0, to be removed in Contao 5.0
			$GLOBALS['TL_LANGUAGE'] = str_replace('_', '-', $this->language);
		}

		$GLOBALS['TL_USERNAME'] = $this->username;

		// Make sure that groups is an array
		if (!is_array($this->groups))
		{
			$this->groups = ($this->groups != '') ? array($this->groups) : array();
		}

		// Skip inactive groups
		if (($objGroups = \MemberGroupModel::findAllActive()) !== null)
		{
			$this->groups = array_intersect($this->groups, $objGroups->fetchEach('id'));
		}

		// Get the group login page
		if ($this->groups[0] > 0)
		{
			$objGroup = \MemberGroupModel::findPublishedById($this->groups[0]);

			if ($objGroup !== null && $objGroup->redirect && $objGroup->jumpTo)
			{
				$this->strLoginPage = $objGroup->jumpTo;
			}
		}
	}
	
}
