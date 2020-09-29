<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Symfony\Component\HttpFoundation\Response;

/**
 * Back end custom controller.
 *
 * @property BackendTemplate|object $Template
 *
 * @author Jim Schmid <https://github.com/sheeep>
 */
class BackendCustom extends BackendMain
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Initialize the template in the constructor so it is available in the getTemplateObject() method
		$this->Template = new \BackendTemplate('be_main');
	}

	/**
	 * Return the template object
	 *
	 * @return BackendTemplate|object
	 */
	public function getTemplateObject()
	{
		return $this->Template;
	}

	/**
	 * Run the controller and parse the template
	 *
	 * @return Response
	 */
	public function run()
	{
		$packages = $this->getContainer()->getParameter('kernel.packages');

		$this->Template->version = $GLOBALS['TL_LANG']['MSC']['version'] . ' ' . (isset($packages['contao/core-bundle']) ? $packages['contao/core-bundle'] : $packages['contao/contao']);

		// Ajax request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax = new \Ajax(\Input::post('action'));
			$this->objAjax->executePreActions();
		}

		return $this->output();
	}
}
