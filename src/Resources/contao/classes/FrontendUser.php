<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


/**
 * Provide methods to manage front end users.
 *
 * @property array   $allGroups
 * @property string  $loginPage
 * @property boolean $blnRecordExists
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FrontendUser extends User
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
	 * Symfony security roles
	 * @var array
	 */
	protected $roles = array('ROLE_MEMBER');


	/**
	 * Initialize the object
	 */
	protected function __construct()
	{
		parent::__construct();

		$this->strIp = \Environment::get('ip');
		$this->strHash = \Input::cookie($this->strCookie);
	}

    public static function getInstance()
    {
        /** @var TokenInterface $token */
        $token = \System::getContainer()->get('security.token_storage')->getToken();

        if ($token !== null && is_a($token->getUser(), get_called_class()))
        {
            return $token->getUser();
        }

        return parent::getInstance();
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
	 *
	 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
	 */
	public function authenticate()
	{
		@trigger_error('Using FrontendUser::authenticate() has been deprecated and will no longer work in Contao 5.0. Use the security.authentication.success event instead.', E_USER_DEPRECATED);

		/** @var TokenInterface $token */
		$token = \System::getContainer()->get('security.token_storage')->getToken();

		// Do not redirect if authentication is successful
		if ($token !== null && $token->getUser() === $this && $token->isAuthenticated())
		{
			// HOOK: post authenticate callback
			if (isset($GLOBALS['TL_HOOKS']['postAuthenticate']) && is_array($GLOBALS['TL_HOOKS']['postAuthenticate']))
			{
				foreach ($GLOBALS['TL_HOOKS']['postAuthenticate'] as $callback)
				{
					\System::importStatic($callback[0])->{$callback[1]}($this);
				}
			}

			return true;
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

			$this->setCookie('FE_AUTO_LOGIN', $strToken, ($time + \Config::get('autologin')), null, null, \Environment::get('ssl'), true);
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
		$this->setCookie('FE_AUTO_LOGIN', $this->autologin, (time() - 86400), null, null, \Environment::get('ssl'), true);

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

		// Set the language
		if ($this->language)
		{
			if (\System::getContainer()->has('session'))
			{
				$session = \System::getContainer()->get('session');

				if ($session->isStarted())
				{
					$session->set('_locale', $this->language);
				}
			}

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

	/**
	 * {@inheritdoc}
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	public static function loadUserByUsername($username)
	{
		$user = new static();

		// Load the user object
		if ($user->findBy('username', $username) === false)
		{
			if (self::triggerImportUserHook($username, $user->strTable) === false) {
				return null;
			}

			if ($user->findBy('username', \Input::post('username') === false)) {
				return null;
			}
		}

		$user->setUserFromDb();

		return $user;
	}
}
