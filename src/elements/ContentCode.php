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


/**
 * Class ContentCode
 *
 * Front end content element "code".
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class ContentCode extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_code';


	/**
	 * Show the raw code in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$return = '<pre>'. specialchars($this->code) .'</pre>';

			if ($this->headline != '')
			{
				$return = '<'. $this->hl .'>'. $this->headline .'</'. $this->hl .'>'. $return;
			}

			return $return;
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		$this->Template->code = htmlspecialchars($this->code);

		// Syntax highlighter
		if ($this->highlight)
		{
			$arrMapper =
			[
				'ApacheConf' => 'shBrushApacheConf',
				'AS3'        => 'shBrushAS3',
				'Bash'       => 'shBrushBash',
				'C'          => 'shBrushCpp',
				'CSharp'     => 'shBrushCSharp',
				'CSS'        => 'shBrushCss',
				'Delphi'     => 'shBrushDelphi',
				'Diff'       => 'shBrushDiff',
				'Groovy'     => 'shBrushGroovy',
				'HTML'       => 'shBrushXml',
				'Java'       => 'shBrushJava',
				'JavaFX'     => 'shBrushJavaFX',
				'JavaScript' => 'shBrushJScript',
				'Perl'       => 'shBrushPerl',
				'PHP'        => 'shBrushPhp',
				'PowerShell' => 'shBrushPowerShell',
				'Python'     => 'shBrushPython',
				'Ruby'       => 'shBrushRuby',
				'Scala'      => 'shBrushScala',
				'SQL'        => 'shBrushSql',
				'Text'       => 'shBrushPlain',
				'VB'         => 'shBrushVb',
				'XHTML'      => 'shBrushXml',
				'XML'        => 'shBrushXml'
			];

			$this->Template->shClass = 'brush: ' . strtolower($this->highlight);

			if ($this->shClass)
			{
				$this->Template->shClass .= '; ' . $this->shClass;
			}

			// Add the style sheet
			$GLOBALS['TL_CSS'][] = 'components/highlighter/css/shCore.css||static';

			// Add the JavaScripts
			$GLOBALS['TL_HIGHLIGHTER'][] = 'components/highlighter/js/XRegExp.js';
			$GLOBALS['TL_HIGHLIGHTER'][] = 'components/highlighter/js/shCore.js';
			$GLOBALS['TL_HIGHLIGHTER'][] = 'components/highlighter/js/' . $arrMapper[$this->highlight] . '.js';

			// The shBrushXml.js file is required for the "html-script" option (see #4748)
			if ($this->shClass != '' && strpos($this->shClass, 'html-script') !== false)
			{
				$GLOBALS['TL_HIGHLIGHTER'][] = 'components/highlighter/js/shBrushXml.js';
			}
		}
	}
}
