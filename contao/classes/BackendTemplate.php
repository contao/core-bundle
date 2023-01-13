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
 * Provide methods to handle back end templates.
 *
 * @property string $ua
 * @property string $javascripts
 * @property string $stylesheets
 * @property string $mootools
 * @property string $attributes
 * @property string $badgeTitle
 */
class BackendTemplate extends Template
{
	use BackendTemplateTrait;

	/**
	 * Add a hook to modify the template output
	 *
	 * @return string
	 */
	public function parse()
	{
		$strBuffer = parent::parse();

		// HOOK: add custom parse filters
		if (isset($GLOBALS['TL_HOOKS']['parseBackendTemplate']) && \is_array($GLOBALS['TL_HOOKS']['parseBackendTemplate']))
		{
			foreach ($GLOBALS['TL_HOOKS']['parseBackendTemplate'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->{$callback[0]}->{$callback[1]}($strBuffer, $this->strTemplate);
			}
		}

		return $strBuffer;
	}

	/**
	 * Return a response object
	 *
	 * @return Response The response object
	 */
	public function getResponse()
	{
		$this->compile();

		$response = parent::getResponse();
		$response->headers->set('Cache-Control', 'no-cache, no-store');

		return $response->setPrivate();
	}

	/**
	 * Compile the template
	 */
	private function compile()
	{
		$this->addBackendConfig();

		// Style sheets
		if (!empty($GLOBALS['TL_CSS']) && \is_array($GLOBALS['TL_CSS']))
		{
			$strStyleSheets = '';
			$objCombiner = new Combiner();

			foreach (array_unique($GLOBALS['TL_CSS']) as $stylesheet)
			{
				$options = StringUtil::resolveFlaggedUrl($stylesheet);

				if ($options->static)
				{
					$objCombiner->add($stylesheet, $options->mtime, $options->media);
				}
				else
				{
					$strStyleSheets .= Template::generateStyleTag($this->addStaticUrlTo($stylesheet), $options->media, $options->mtime);
				}
			}

			if ($objCombiner->hasEntries())
			{
				$strStyleSheets = Template::generateStyleTag($objCombiner->getCombinedFile(), 'all') . $strStyleSheets;
			}

			$this->stylesheets .= $strStyleSheets;
		}

		// JavaScripts
		if (!empty($GLOBALS['TL_JAVASCRIPT']) && \is_array($GLOBALS['TL_JAVASCRIPT']))
		{
			$objCombiner = new Combiner();
			$objCombinerAsync = new Combiner();
			$strJavaScripts = '';

			foreach (array_unique($GLOBALS['TL_JAVASCRIPT']) as $javascript)
			{
				$options = StringUtil::resolveFlaggedUrl($javascript);

				if ($options->static)
				{
					$options->async ? $objCombinerAsync->add($javascript, $options->mtime) : $objCombiner->add($javascript, $options->mtime);
				}
				else
				{
					$strJavaScripts .= Template::generateScriptTag($this->addStaticUrlTo($javascript), $options->async, $options->mtime);
				}
			}

			if ($objCombiner->hasEntries())
			{
				$strJavaScripts = Template::generateScriptTag($objCombiner->getCombinedFile()) . $strJavaScripts;
			}

			if ($objCombinerAsync->hasEntries())
			{
				$strJavaScripts = Template::generateScriptTag($objCombinerAsync->getCombinedFile(), true) . $strJavaScripts;
			}

			$this->javascripts .= $strJavaScripts;
		}

		// MooTools scripts (added at the page bottom)
		if (!empty($GLOBALS['TL_MOOTOOLS']) && \is_array($GLOBALS['TL_MOOTOOLS']))
		{
			$this->mootools .= implode('', array_unique($GLOBALS['TL_MOOTOOLS']));
		}

		$strBuffer = $this->parse();

		// HOOK: add custom output filter
		if (isset($GLOBALS['TL_HOOKS']['outputBackendTemplate']) && \is_array($GLOBALS['TL_HOOKS']['outputBackendTemplate']))
		{
			foreach ($GLOBALS['TL_HOOKS']['outputBackendTemplate'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->{$callback[0]}->{$callback[1]}($strBuffer, $this->strTemplate);
			}
		}

		$this->strBuffer = $strBuffer;
	}

	/**
	 * Add the contao.backend configuration
	 */
	private function addBackendConfig(): void
	{
		$container = System::getContainer();

		if ($container->hasParameter('contao.backend.attributes'))
		{
			$attributes = $container->getParameter('contao.backend.attributes');

			if (!empty($attributes) && \is_array($attributes))
			{
				$this->attributes = ' ' . implode(' ', array_map(
					static function ($v, $k) { return sprintf('data-%s="%s"', $k, $v); },
					$attributes,
					array_keys($attributes)
				));
			}
		}

		if ($container->hasParameter('contao.backend.custom_css'))
		{
			$css = $container->getParameter('contao.backend.custom_css');

			if (!empty($css) && \is_array($css))
			{
				if (!\is_array($GLOBALS['TL_CSS']))
				{
					$GLOBALS['TL_CSS'] = array();
				}

				$GLOBALS['TL_CSS'] = array_merge($GLOBALS['TL_CSS'], $css);
			}
		}

		if ($container->hasParameter('contao.backend.custom_js'))
		{
			$js = $container->getParameter('contao.backend.custom_js');

			if (!empty($js) && \is_array($js))
			{
				if (!\is_array($GLOBALS['TL_JAVASCRIPT']))
				{
					$GLOBALS['TL_JAVASCRIPT'] = array();
				}

				$GLOBALS['TL_JAVASCRIPT'] = array_merge($GLOBALS['TL_JAVASCRIPT'], $js);
			}
		}

		if ($container->hasParameter('contao.backend.badge_title'))
		{
			$this->badgeTitle = $container->getParameter('contao.backend.badge_title');
		}
	}
}
