<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;


/**
 * Provide methods to handle a logout page.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PageLogout extends \Frontend
{

	/**
	 * Return a redirect response object
	 *
	 * @param PageModel $objPage
	 *
	 * @return RedirectResponse
	 */
	public function getResponse($objPage)
	{
		// Set last page visited
		if ($objPage->redirectBack)
		{
			$_SESSION['LAST_PAGE_VISITED'] = $this->getReferer();
		}

		$strRedirect = \Environment::get('base');

		// Redirect to last page visited
		if ($objPage->redirectBack && !empty($_SESSION['LAST_PAGE_VISITED']))
		{
			$strRedirect = $_SESSION['LAST_PAGE_VISITED'];
		}

		// Redirect to jumpTo page
		elseif ($objPage->jumpTo && ($objTarget = $objPage->getRelated('jumpTo')) instanceof PageModel)
		{
			/** @var PageModel $objTarget */
			$strRedirect = $objTarget->getAbsoluteUrl();
		}

		/** @var Session $session */
		$session = System::getContainer()->get('session');
		$session->set('_contao_logout_target', $strRedirect);

		/** @var RouterInterface $router */
		$router = System::getContainer()->get('router');

		return new RedirectResponse($router->generate('contao_frontend_logout'));
	}
}
