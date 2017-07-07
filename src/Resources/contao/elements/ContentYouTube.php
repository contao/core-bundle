<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
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
			return '<p><a href="https://youtu.be/' . $this->youtube . '" target="_blank">youtu.be/' . $this->youtube . '</a></p>';
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$size = \StringUtil::deserialize($this->playerSize);

		if (!is_array($size) || empty($size[0]) || empty($size[1]))
		{
			$this->Template->size = ' width="640" height="360"';
		}
		else
		{
			$this->Template->size = ' width="' . $size[0] . '" height="' . $size[1] . '"';
		}

		$this->Template->src = $this->getYouTubeUrl();
	}

	/**
	 * Gets YouTube URL based on configuration options.
	 *
	 * @return string
	 */
	private function getYouTubeUrl()
	{
		$query = array();
		$options = \StringUtil::deserialize($this->youtubeOptions, true);

		if ($this->autoplay)
		{
			$query['autoplay'] = '1';
		}

		if (!in_array('youtube_suggest', $options, true))
		{
			$query['rel'] = '0';
		}

		if (!in_array('youtube_controls', $options, true))
		{
			$query['controls'] = '0';
		}

		if (!in_array('youtube_intro', $options, true))
		{
			$query['showintro'] = '0';
		}

		$domain = in_array('youtube_privacy', $options, true) ? 'youtube-nocookie' : 'youtube';
		$url = 'https://www.' . $domain . '.com/embed/' . $this->youtube;

		if (!empty($query))
		{
			$url .= '?' . http_build_query($query);
		}

		return $url;
	}
}
