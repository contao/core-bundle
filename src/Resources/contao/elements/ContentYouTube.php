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
 * Content element "YouTube".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContentYouTube extends \ContentElement
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_youtube';

	/**
	 * Show the YouTube link in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		if ($this->youtube == '')
		{
			return '';
		}

		if (TL_MODE == 'BE')
		{
			$return = '<p><a href="https://youtu.be/' . $this->youtube . '" target="_blank" rel="noreferrer noopener">youtu.be/' . $this->youtube . '</a></p>';

			if ($this->headline != '')
			{
				$return = '<' . $this->hl . '>' . $this->headline . '</' . $this->hl . '>' . $return;
			}

			return $return;
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$size = \StringUtil::deserialize($this->playerSize);

		if (!\is_array($size) || empty($size[0]) || empty($size[1]))
		{
			$this->Template->size = ' width="640" height="360"';
		}
		else
		{
			$this->Template->size = ' width="' . $size[0] . '" height="' . $size[1] . '"';
		}

		$url = 'https://www.youtube.com/embed/' . $this->youtube;

		if ($this->autoplay)
		{
			$url .= '?autoplay=1';
		}

		$this->Template->src = $url;
	}
}
