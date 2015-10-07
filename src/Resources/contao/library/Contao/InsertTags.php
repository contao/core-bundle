<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


/**
 * A static class to replace insert tags
 *
 * Usage:
 *
 *     $it = new InsertTags();
 *     echo $it->replace($text);
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated TODO
 */
class InsertTags extends \Controller
{

	/**
	 * Make the constructor public
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Replace insert tags with their values
	 *
	 * @param string  $strBuffer The text with the tags to be replaced
	 * @param boolean $blnCache  If false, non-cacheable tags will be replaced
	 *
	 * @return string The text with the replaced tags
	 */
	public function replace($strBuffer, $blnCache=true)
	{
		$request = \System::getContainer()
			->get('request_stack')
			->getCurrentRequest();

		return \System::getContainer()
			->get('contao.insert_tag_parser.string')
			->parse($strBuffer, $request);
	}
}
