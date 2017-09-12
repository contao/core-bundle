<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


/**
 * Handle back end logins and logouts.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BackendIndex extends \Backend
{
	/** @var ContainerInterface $container */
	protected $container;

	/** @var FlashBagInterface $flashBag */
	protected $flashBag;

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
		$this->container = System::getContainer();
		$this->flashBag = $this->container->get('session')->getFlashBag();

		/** @var AuthorizationCheckerInterface $authorizationChecker */
		$authorizationChecker = $this->container->get('security.authorization_checker');

		/** @var TokenInterface $token */
		$token = $this->container->get('security.token_storage')->getToken();

		if ($token !== null && ($user = $token->getUser()) instanceof FrontendUser)
		{
			/** @var UserInterface $user */
			if ($authorizationChecker->isGranted($user->getRoles()))
			{
				return $this->redirect('/logout');
			}
		}

		$this->import('BackendUser', 'User');
		parent::__construct();

		// Login
		$this->User->authenticate();

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
		$this->checkAuthentication();

		/** @var BackendTemplate|object $objTemplate */
		$objTemplate = new \BackendTemplate('be_login');

		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->messages = \Message::generate();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->languages = \System::getLanguages(true);
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

		if ($this->flashBag->has('be_login'))
		{
			$flashes = $this->flashBag->get('be_login');

			$objTemplate->message = $flashes[0];
		}

		return $objTemplate->getResponse();
	}

    protected function checkAuthentication()
    {
        /** @var AuthenticationUtils $authenticationUtils */
        $authenticationUtils = $this->container->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error instanceof DisabledException || $error instanceof AccountExpiredException || $error instanceof BadCredentialsException)
        {
            $this->flashBag->set('be_login', $GLOBALS['TL_LANG']['ERR']['invalidLogin']);
        }

        elseif ($error instanceof LockedException)
        {
            $time = time();

            /** @var TokenStorageInterface $tokenStorage */
            $tokenStorage = $this->container->get('security.token_storage');

            $user = $tokenStorage->getToken()->getUser();

            $this->flashBag->set('be_login', sprintf(
                $GLOBALS['TL_LANG']['ERR']['accountLocked'],
                ceil((($user->locked + Config::get('lockPeriod')) - $time) / 60)
            ));
        }

        elseif ($error instanceof \Exception)
        {
            throw $error;
        }
    }
}
