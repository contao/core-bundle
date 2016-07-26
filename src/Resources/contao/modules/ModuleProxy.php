<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

/**
 * Front end module "proxy".
 * Proxies new Symfony controllers for the Contao legacy module system.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class ModuleProxy extends \Module
{
	/**
	 * Do not display the module if there are no articles
	 *
	 * @return string
	 */
	public function generate()
	{
		return \Controller::getFrontendModule($this->id, $this->inColumn);
	}

	/**
	 * Compile the current element
	 */
	protected function compile()
	{
		// noop
	}
}
