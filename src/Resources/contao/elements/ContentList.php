<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Front end content element "list".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentList extends \ContentElement
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_list';

	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		$arrItems = array();
		$items = \StringUtil::deserialize($this->listitems, true);
		$limit = \count($items) - 1;

		for ($i=0, $c=\count($items); $i<$c; $i++)
		{
			$arrItems[] = array
			(
				'class' => (($i == 0) ? 'first' : (($i == $limit) ? 'last' : '')),
				'content' => $items[$i]
			);
		}

		$this->Template->items = $arrItems;
		$this->Template->tag = ($this->listtype == 'ordered') ? 'ol' : 'ul';
	}
}
