<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao;

use Michelf\MarkdownExtra;


/**
 * Class BackendChangelog
 *
 * Show the changelog to authenticated users.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class BackendChangelog extends Backend
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

		$this->User->authenticate();
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		// Parse the changelog file
		$strBuffer = file_get_contents(TL_ROOT . '/system/docs/CHANGELOG.md');

		// Remove carriage returns (see #4190)
		$strBuffer = str_replace("\r", '', $strBuffer);

		// Convert to HTML
		$strBuffer = MarkdownExtra::defaultTransform($strBuffer);

		// Add the template
		$objTemplate = new BackendTemplate('be_changelog');

		// Assign the template variables
		$objTemplate->content = $strBuffer;
		$objTemplate->theme = Backend::getTheme();
		$objTemplate->base = Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title = specialchars($GLOBALS['TL_LANG']['MSC']['changelog']);
		$objTemplate->charset = Config::get('characterSet');

		Config::set('debugMode', false);
		return $objTemplate->getResponse();
	}
}
