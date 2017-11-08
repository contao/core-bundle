<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;


/**
 * A static class to replace insert tags
 *
 * Usage:
 *
 *     $it = new InsertTags();
 *     echo $it->replace($text);
 *
 * @author Leo Feyer <https://github.com/leofeyer>
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
	 * Recursively replace insert tags with their values
	 *
	 * @param string  $strBuffer The text with the tags to be replaced
	 * @param boolean $blnCache  If false, non-cacheable tags will be replaced
	 *
	 * @return string The text with the replaced tags
	 */
	public function replace($strBuffer, $blnCache=true)
	{
		$strBuffer = $this->doReplace($strBuffer, $blnCache);

		// Run the replacement recursively (see #8172)
		while (strpos($strBuffer, '{{') !== false && ($strTmp = $this->doReplace($strBuffer, $blnCache)) != $strBuffer)
		{
			$strBuffer = $strTmp;
		}

		return $strBuffer;
	}


	/**
	 * Replace insert tags with their values
	 *
	 * @param string  $strBuffer The text with the tags to be replaced
	 * @param boolean $blnCache  If false, non-cacheable tags will be replaced
	 *
	 * @return string The text with the replaced tags
	 */
	protected function doReplace($strBuffer, $blnCache)
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Preserve insert tags
		if (\Config::get('disableInsertTags'))
		{
			return \StringUtil::restoreBasicEntities($strBuffer);
		}

		// The first letter must not be a reserved character of Twig, Mustache or similar template engines (see #805)
		$tags = preg_split('~{{([\pL\pN][^{}]*)}}~u', $strBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

		if (\count($tags) < 2)
		{
			return \StringUtil::restoreBasicEntities($strBuffer);
		}

		$strBuffer = '';

		// Create one cache per cache setting (see #7700)
		static $arrItCache;
		$arrCache = &$arrItCache[$blnCache];

		for ($_rit=0, $_cnt=\count($tags); $_rit<$_cnt; $_rit+=2)
		{
			$strBuffer .= $tags[$_rit];
			$strTag = $tags[$_rit+1];

			// Skip empty tags
			if ($strTag == '')
			{
				continue;
			}

			$flags = explode('|', $strTag);
			$tag = array_shift($flags);
			$elements = explode('::', $tag);

			// Load the value from cache
			if (isset($arrCache[$strTag]) && !\in_array('refresh', $flags))
			{
				$strBuffer .= $arrCache[$strTag];
				continue;
			}

			// Skip certain elements if the output will be cached
			if ($blnCache)
			{
				if ($elements[0] == 'date' || $elements[0] == 'ua' || $elements[0] == 'post' || $elements[1] == 'back' || $elements[1] == 'referer' || $elements[0] == 'request_token' || $elements[0] == 'toggle_view' || strncmp($elements[0], 'cache_', 6) === 0 || \in_array('uncached', $flags))
				{
					/** @var FragmentHandler $fragmentHandler */
					$fragmentHandler = \System::getContainer()->get('fragment.handler');

					$strBuffer .= $fragmentHandler->render(
						new ControllerReference(
							'contao.controller.insert_tags:renderAction',
							['insertTag' => '{{' . $strTag . '}}'],
							['pageId' => $objPage->id, 'request' => \Environment::get('request')]
						),
						'esi'
					);

					continue;
				}
			}

			$arrCache[$strTag] = '';

			// Replace the tag
			switch (strtolower($elements[0]))
			{
				// Date
				case 'date':
					$arrCache[$strTag] = \Date::parse($elements[1] ?: \Config::get('dateFormat'));
					break;

				// Accessibility tags
				case 'lang':
					if ($elements[1] == '')
					{
						$arrCache[$strTag] = '</span>';
					}
					else
					{
						$arrCache[$strTag] = $arrCache[$strTag] = '<span lang="' . \StringUtil::specialchars($elements[1]) . '">';
					}
					break;

				// Line break
				case 'br':
					$arrCache[$strTag] = '<br>';
					break;

				// E-mail addresses
				case 'email':
				case 'email_open':
				case 'email_url':
					if ($elements[1] == '')
					{
						$arrCache[$strTag] = '';
						break;
					}

					$strEmail = \StringUtil::encodeEmail($elements[1]);

					// Replace the tag
					switch (strtolower($elements[0]))
					{
						case 'email':
							$arrCache[$strTag] = '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . $strEmail . '" class="email">' . preg_replace('/\?.*$/', '', $strEmail) . '</a>';
							break;

						case 'email_open':
							$arrCache[$strTag] = '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . $strEmail . '" title="' . $strEmail . '" class="email">';
							break;

						case 'email_url':
							$arrCache[$strTag] = $strEmail;
							break;
					}
					break;

				// Label tags
				case 'label':
					$keys = explode(':', $elements[1]);

					if (\count($keys) < 2)
					{
						$arrCache[$strTag] = '';
						break;
					}

					$file = $keys[0];

					// Map the key (see #7217)
					switch ($file)
					{
						case 'CNT':
							$file = 'countries';
							break;

						case 'LNG':
							$file = 'languages';
							break;

						case 'MOD':
						case 'FMD':
							$file = 'modules';
							break;

						case 'FFL':
							$file = 'tl_form_field';
							break;

						case 'CACHE':
							$file = 'tl_page';
							break;

						case 'XPL':
							$file = 'explain';
							break;

						case 'XPT':
							$file = 'exception';
							break;

						case 'MSC':
						case 'ERR':
						case 'CTE':
						case 'PTY':
						case 'FOP':
						case 'CHMOD':
						case 'DAYS':
						case 'MONTHS':
						case 'UNITS':
						case 'CONFIRM':
						case 'DP':
						case 'COLS':
							$file = 'default';
							break;
					}

					\System::loadLanguageFile($file);

					if (\count($keys) == 2)
					{
						$arrCache[$strTag] = $GLOBALS['TL_LANG'][$keys[0]][$keys[1]];
					}
					else
					{
						$arrCache[$strTag] = $GLOBALS['TL_LANG'][$keys[0]][$keys[1]][$keys[2]];
					}
					break;

				// Front end user
				case 'user':
					if (FE_USER_LOGGED_IN)
					{
						$this->import('FrontendUser', 'User');
						$value = $this->User->{$elements[1]};

						if ($value == '')
						{
							$arrCache[$strTag] = $value;
							break;
						}

						$this->loadDataContainer('tl_member');

						if ($GLOBALS['TL_DCA']['tl_member']['fields'][$elements[1]]['inputType'] == 'password')
						{
							$arrCache[$strTag] = '';
							break;
						}

						$value = \StringUtil::deserialize($value);

						// Decrypt the value
						if ($GLOBALS['TL_DCA']['tl_member']['fields'][$elements[1]]['eval']['encrypt'])
						{
							$value = \Encryption::decrypt($value);
						}

						$rgxp = $GLOBALS['TL_DCA']['tl_member']['fields'][$elements[1]]['eval']['rgxp'];
						$opts = $GLOBALS['TL_DCA']['tl_member']['fields'][$elements[1]]['options'];
						$rfrc = $GLOBALS['TL_DCA']['tl_member']['fields'][$elements[1]]['reference'];

						if ($rgxp == 'date')
						{
							$arrCache[$strTag] = \Date::parse(\Config::get('dateFormat'), $value);
						}
						elseif ($rgxp == 'time')
						{
							$arrCache[$strTag] = \Date::parse(\Config::get('timeFormat'), $value);
						}
						elseif ($rgxp == 'datim')
						{
							$arrCache[$strTag] = \Date::parse(\Config::get('datimFormat'), $value);
						}
						elseif (\is_array($value))
						{
							$arrCache[$strTag] = implode(', ', $value);
						}
						elseif (\is_array($opts) && array_is_assoc($opts))
						{
							$arrCache[$strTag] = isset($opts[$value]) ? $opts[$value] : $value;
						}
						elseif (\is_array($rfrc))
						{
							$arrCache[$strTag] = isset($rfrc[$value]) ? ((\is_array($rfrc[$value])) ? $rfrc[$value][0] : $rfrc[$value]) : $value;
						}
						else
						{
							$arrCache[$strTag] = $value;
						}

						// Convert special characters (see #1890)
						$arrCache[$strTag] = \StringUtil::specialchars($arrCache[$strTag]);
					}
					break;

				// Link
				case 'link':
				case 'link_open':
				case 'link_url':
				case 'link_title':
				case 'link_target':
				case 'link_name':
					$strTarget = null;

					// Back link
					if ($elements[1] == 'back')
					{
						$strUrl = 'javascript:history.go(-1)';
						$strTitle = $GLOBALS['TL_LANG']['MSC']['goBack'];

						// No language files if the page is cached
						if (!\strlen($strTitle))
						{
							$strTitle = 'Go back';
						}

						$strName = $strTitle;
					}

					// External links
					elseif (strncmp($elements[1], 'http://', 7) === 0 || strncmp($elements[1], 'https://', 8) === 0)
					{
						$strUrl = $elements[1];
						$strTitle = $elements[1];
						$strName = str_replace(array('http://', 'https://'), '', $elements[1]);
					}

					// Regular link
					else
					{
						// User login page
						if ($elements[1] == 'login')
						{
							if (!FE_USER_LOGGED_IN)
							{
								break;
							}

							$this->import('FrontendUser', 'User');
							$elements[1] = $this->User->loginPage;
						}

						$objNextPage = \PageModel::findByIdOrAlias($elements[1]);

						if ($objNextPage === null)
						{
							break;
						}

						// Page type specific settings (thanks to Andreas Schempp)
						switch ($objNextPage->type)
						{
							case 'redirect':
								$strUrl = $objNextPage->url;

								if (strncasecmp($strUrl, 'mailto:', 7) === 0)
								{
									$strUrl = \StringUtil::encodeEmail($strUrl);
								}
								break;

							case 'forward':
								if ($objNextPage->jumpTo)
								{
									/** @var PageModel $objNext */
									$objNext = $objNextPage->getRelated('jumpTo');
								}
								else
								{
									$objNext = \PageModel::findFirstPublishedRegularByPid($objNextPage->id);
								}

								if ($objNext instanceof PageModel)
								{
									$strUrl = \in_array('absolute', $flags, true) ? $objNext->getAbsoluteUrl() : $objNext->getFrontendUrl();
									break;
								}
								// DO NOT ADD A break; STATEMENT

							default:
								$strUrl = \in_array('absolute', $flags, true) ? $objNextPage->getAbsoluteUrl() : $objNextPage->getFrontendUrl();
								break;
						}

						$strName = $objNextPage->title;
						$strTarget = $objNextPage->target ? ' target="_blank"' : '';
						$strTitle = $objNextPage->pageTitle ?: $objNextPage->title;
					}

					// Replace the tag
					switch (strtolower($elements[0]))
					{
						case 'link':
							$arrCache[$strTag] = sprintf('<a href="%s" title="%s"%s>%s</a>', $strUrl, \StringUtil::specialchars($strTitle), $strTarget, $strName);
							break;

						case 'link_open':
							$arrCache[$strTag] = sprintf('<a href="%s" title="%s"%s>', $strUrl, \StringUtil::specialchars($strTitle), $strTarget);
							break;

						case 'link_url':
							$arrCache[$strTag] = $strUrl;
							break;

						case 'link_title':
							$arrCache[$strTag] = \StringUtil::specialchars($strTitle);
							break;

						case 'link_target':
							$arrCache[$strTag] = $strTarget;
							break;

						case 'link_name':
							$arrCache[$strTag] = $strName;
							break;
					}
					break;

				// Closing link tag
				case 'link_close':
				case 'email_close':
					$arrCache[$strTag] = '</a>';
					break;

				// Insert article
				case 'insert_article':
					if (($strOutput = $this->getArticle($elements[1], false, true)) !== false)
					{
						$arrCache[$strTag] = ltrim($strOutput);
					}
					else
					{
						$arrCache[$strTag] = '<p class="error">' . sprintf($GLOBALS['TL_LANG']['MSC']['invalidPage'], $elements[1]) . '</p>';
					}
					break;

				// Insert content element
				case 'insert_content':
					$arrCache[$strTag] = $this->getContentElement($elements[1]);
					break;

				// Insert module
				case 'insert_module':
					$arrCache[$strTag] = $this->getFrontendModule($elements[1]);
					break;

				// Insert form
				case 'insert_form':
					$arrCache[$strTag] = $this->getForm($elements[1]);
					break;

				// Article
				case 'article':
				case 'article_open':
				case 'article_url':
				case 'article_title':
					if (($objArticle = \ArticleModel::findByIdOrAlias($elements[1])) === null || !(($objPid = $objArticle->getRelated('pid')) instanceof PageModel))
					{
						break;
					}

					/** @var PageModel $objPid */
					$params = '/articles/' . ($objArticle->alias ?: $objArticle->id);
					$strUrl = \in_array('absolute', $flags, true) ? $objPid->getAbsoluteUrl($params) : $objPid->getFrontendUrl($params);

					// Replace the tag
					switch (strtolower($elements[0]))
					{
						case 'article':
							$arrCache[$strTag] = sprintf('<a href="%s" title="%s">%s</a>', $strUrl, \StringUtil::specialchars($objArticle->title), $objArticle->title);
							break;

						case 'article_open':
							$arrCache[$strTag] = sprintf('<a href="%s" title="%s">', $strUrl, \StringUtil::specialchars($objArticle->title));
							break;

						case 'article_url':
							$arrCache[$strTag] = $strUrl;
							break;

						case 'article_title':
							$arrCache[$strTag] = \StringUtil::specialchars($objArticle->title);
							break;
					}
					break;

				// Article teaser
				case 'article_teaser':
					$objTeaser = \ArticleModel::findByIdOrAlias($elements[1]);

					if ($objTeaser !== null)
					{
						$arrCache[$strTag] = \StringUtil::toHtml5($objTeaser->teaser);
					}
					break;

				// Last update
				case 'last_update':
					$strQuery = "SELECT MAX(tstamp) AS tc";
					$bundles = \System::getContainer()->getParameter('kernel.bundles');

					if (isset($bundles['ContaoNewsBundle']))
					{
						$strQuery .= ", (SELECT MAX(tstamp) FROM tl_news) AS tn";
					}

					if (isset($bundles['ContaoCalendarBundle']))
					{
						$strQuery .= ", (SELECT MAX(tstamp) FROM tl_calendar_events) AS te";
					}

					$strQuery .= " FROM tl_content";
					$objUpdate = \Database::getInstance()->query($strQuery);

					if ($objUpdate->numRows)
					{
						$arrCache[$strTag] = \Date::parse($elements[1] ?: \Config::get('datimFormat'), max($objUpdate->tc, $objUpdate->tn, $objUpdate->te));
					}
					break;

				// Version
				case 'version':
					$arrCache[$strTag] = VERSION . '.' . BUILD;
					break;

				// Request token
				case 'request_token':
					$arrCache[$strTag] = REQUEST_TOKEN;
					break;

				// POST data
				case 'post':
					$arrCache[$strTag] = \Input::post($elements[1]);
					break;

				// Mobile/desktop toggle (see #6469)
				case 'toggle_view':
					$strRequest = \Environment::get('request');

					// ESI request
					if (preg_match('/^' . preg_quote(ltrim(\System::getContainer()->getParameter('fragment.path'), '/'), '/') . '/', $strRequest))
					{
						$request = \System::getContainer()->get('request_stack')->getCurrentRequest();
						$strRequest = $request->query->get('request');
					}

					$strUrl = ampersand($strRequest);
					$strGlue = (strpos($strUrl, '?') === false) ? '?' : '&amp;';

					\System::loadLanguageFile('default');

					if (\Input::cookie('TL_VIEW') == 'mobile' || (\Environment::get('agent')->mobile && \Input::cookie('TL_VIEW') != 'desktop'))
					{
						$arrCache[$strTag] = '<a href="' . $strUrl . $strGlue . 'toggle_view=desktop" class="toggle_desktop" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['toggleDesktop'][1]) . '">' . $GLOBALS['TL_LANG']['MSC']['toggleDesktop'][0] . '</a>';
					}
					else
					{
						$arrCache[$strTag] = '<a href="' . $strUrl . $strGlue . 'toggle_view=mobile" class="toggle_mobile" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['toggleMobile'][1]) . '">' . $GLOBALS['TL_LANG']['MSC']['toggleMobile'][0] . '</a>';
					}
					break;

				// Conditional tags (if)
				case 'iflng':
					if ($elements[1] != '' && $elements[1] != $objPage->language)
					{
						for (; $_rit<$_cnt; $_rit+=2)
						{
							if ($tags[$_rit+1] == 'iflng' || $tags[$_rit+1] == 'iflng::' . $objPage->language)
							{
								break;
							}
						}
					}
					unset($arrCache[$strTag]);
					break;

				// Conditional tags (if not)
				case 'ifnlng':
					if ($elements[1] != '')
					{
						$langs = \StringUtil::trimsplit(',', $elements[1]);

						if (\in_array($objPage->language, $langs))
						{
							for (; $_rit<$_cnt; $_rit+=2)
							{
								if ($tags[$_rit+1] == 'ifnlng')
								{
									break;
								}
							}
						}
					}
					unset($arrCache[$strTag]);
					break;

				// Environment
				case 'env':
					switch ($elements[1])
					{
						case 'host':
							$arrCache[$strTag] = \Idna::decode(\Environment::get('host'));
							break;

						case 'http_host':
							$arrCache[$strTag] = \Idna::decode(\Environment::get('httpHost'));
							break;

						case 'url':
							$arrCache[$strTag] = \Idna::decode(\Environment::get('url'));
							break;

						case 'path':
							$arrCache[$strTag] = \Idna::decode(\Environment::get('base'));
							break;

						case 'request':
							$arrCache[$strTag] = \Environment::get('indexFreeRequest');
							break;

						case 'ip':
							$arrCache[$strTag] = \Environment::get('ip');
							break;

						case 'referer':
							$arrCache[$strTag] = $this->getReferer(true);
							break;

						case 'files_url':
							$arrCache[$strTag] = TL_FILES_URL;
							break;

						case 'assets_url':
						case 'plugins_url':
						case 'script_url':
							$arrCache[$strTag] = TL_ASSETS_URL;
							break;

						case 'base_url':
							$arrCache[$strTag] = \System::getContainer()->get('request_stack')->getCurrentRequest()->getBaseUrl();
							break;
					}
					break;

				// Page
				case 'page':
					if ($elements[1] == 'pageTitle' && $objPage->pageTitle == '')
					{
						$elements[1] = 'title';
					}
					elseif ($elements[1] == 'parentPageTitle' && $objPage->parentPageTitle == '')
					{
						$elements[1] = 'parentTitle';
					}
					elseif ($elements[1] == 'mainPageTitle' && $objPage->mainPageTitle == '')
					{
						$elements[1] = 'mainTitle';
					}

					// Do not use \StringUtil::specialchars() here (see #4687)
					$arrCache[$strTag] = $objPage->{$elements[1]};
					break;

				// User agent
				case 'ua':
					$ua = \Environment::get('agent');

					if ($elements[1] != '')
					{
						$arrCache[$strTag] = $ua->{$elements[1]};
					}
					else
					{
						$arrCache[$strTag] = '';
					}
					break;

				// Abbreviations
				case 'abbr':
				case 'acronym':
					if ($elements[1] != '')
					{
						$arrCache[$strTag] = '<abbr title="'. \StringUtil::specialchars($elements[1]) .'">';
					}
					else
					{
						$arrCache[$strTag] = '</abbr>';
					}
					break;

				// Images
				case 'image':
				case 'picture':
					$width = null;
					$height = null;
					$alt = '';
					$class = '';
					$rel = '';
					$strFile = $elements[1];
					$mode = '';
					$size = null;
					$strTemplate = 'picture_default';

					// Take arguments
					if (strpos($elements[1], '?') !== false)
					{
						$arrChunks = explode('?', urldecode($elements[1]), 2);
						$strSource = \StringUtil::decodeEntities($arrChunks[1]);
						$strSource = str_replace('[&]', '&', $strSource);
						$arrParams = explode('&', $strSource);

						foreach ($arrParams as $strParam)
						{
							list($key, $value) = explode('=', $strParam);

							switch ($key)
							{
								case 'width':
									$width = $value;
									break;

								case 'height':
									$height = $value;
									break;

								case 'alt':
									$alt = $value;
									break;

								case 'class':
									$class = $value;
									break;

								case 'rel':
									$rel = $value;
									break;

								case 'mode':
									$mode = $value;
									break;

								case 'size':
									$size = (int) $value;
									break;

								case 'template':
									$strTemplate = preg_replace('/[^a-z0-9_]/i', '', $value);
									break;
							}
						}

						$strFile = $arrChunks[0];
					}

					if (\Validator::isUuid($strFile))
					{
						// Handle UUIDs
						$objFile = \FilesModel::findByUuid($strFile);

						if ($objFile === null)
						{
							$arrCache[$strTag] = '';
							break;
						}

						$strFile = $objFile->path;
					}
					elseif (is_numeric($strFile))
					{
						// Handle numeric IDs (see #4805)
						$objFile = \FilesModel::findByPk($strFile);

						if ($objFile === null)
						{
							$arrCache[$strTag] = '';
							break;
						}

						$strFile = $objFile->path;
					}
					else
					{
						// Check the path
						if (\Validator::isInsecurePath($strFile))
						{
							throw new \RuntimeException('Invalid path ' . $strFile);
						}
					}

					// Check the maximum image width
					if (\Config::get('maxImageWidth') > 0 && $width > \Config::get('maxImageWidth'))
					{
						$width = \Config::get('maxImageWidth');
						$height = null;
					}

					// Generate the thumbnail image
					try
					{
						// Image
						if (strtolower($elements[0]) == 'image')
						{
							$dimensions = '';
							$src = \System::getContainer()->get('contao.image.image_factory')->create(TL_ROOT . '/' . rawurldecode($strFile), array($width, $height, $mode))->getUrl(TL_ROOT);
							$objFile = new \File(rawurldecode($src));

							// Add the image dimensions
							if (($imgSize = $objFile->imageSize) !== false)
							{
								$dimensions = ' width="' . \StringUtil::specialchars($imgSize[0]) . '" height="' . \StringUtil::specialchars($imgSize[1]) . '"';
							}

							$arrCache[$strTag] = '<img src="' . TL_FILES_URL . $src . '" ' . $dimensions . ' alt="' . \StringUtil::specialchars($alt) . '"' . (($class != '') ? ' class="' . \StringUtil::specialchars($class) . '"' : '') . '>';
						}

						// Picture
						else
						{
							$picture = \System::getContainer()->get('contao.image.picture_factory')->create(TL_ROOT . '/' . $strFile, $size);

							$picture = array
							(
								'img' => $picture->getImg(TL_ROOT, TL_FILES_URL),
								'sources' => $picture->getSources(TL_ROOT, TL_FILES_URL)
							);

							$picture['alt'] = $alt;
							$picture['class'] = $class;
							$pictureTemplate = new \FrontendTemplate($strTemplate);
							$pictureTemplate->setData($picture);
							$arrCache[$strTag] = $pictureTemplate->parse();
						}

						// Add a lightbox link
						if ($rel != '')
						{
							if (strncmp($rel, 'lightbox', 8) !== 0)
							{
								$attribute = ' rel="' . \StringUtil::specialchars($rel) . '"';
							}
							else
							{
								$attribute = ' data-lightbox="' . \StringUtil::specialchars(substr($rel, 8)) . '"';
							}

							$arrCache[$strTag] = '<a href="' . TL_FILES_URL . $strFile . '"' . (($alt != '') ? ' title="' . \StringUtil::specialchars($alt) . '"' : '') . $attribute . '>' . $arrCache[$strTag] . '</a>';
						}
					}
					catch (\Exception $e)
					{
						$arrCache[$strTag] = '';
					}
					break;

				// Files (UUID or template path)
				case 'file':
					if (\Validator::isUuid($elements[1]))
					{
						$objFile = \FilesModel::findByUuid($elements[1]);

						if ($objFile !== null)
						{
							$arrCache[$strTag] = $objFile->path;
							break;
						}
					}

					$arrGet = $_GET;
					\Input::resetCache();
					$strFile = $elements[1];

					// Take arguments and add them to the $_GET array
					if (strpos($elements[1], '?') !== false)
					{
						$arrChunks = explode('?', urldecode($elements[1]));
						$strSource = \StringUtil::decodeEntities($arrChunks[1]);
						$strSource = str_replace('[&]', '&', $strSource);
						$arrParams = explode('&', $strSource);

						foreach ($arrParams as $strParam)
						{
							$arrParam = explode('=', $strParam);
							$_GET[$arrParam[0]] = $arrParam[1];
						}

						$strFile = $arrChunks[0];
					}

					// Check the path
					if (\Validator::isInsecurePath($strFile))
					{
						throw new \RuntimeException('Invalid path ' . $strFile);
					}

					// Include .php, .tpl, .xhtml and .html5 files
					if (preg_match('/\.(php|tpl|xhtml|html5)$/', $strFile) && file_exists(TL_ROOT . '/templates/' . $strFile))
					{
						ob_start();

						try
						{
							include TL_ROOT . '/templates/' . $strFile;
							$arrCache[$strTag] = ob_get_contents();
						}
						finally
						{
							ob_end_clean();
						}
					}

					$_GET = $arrGet;
					\Input::resetCache();
					break;

				// HOOK: pass unknown tags to callback functions
				default:
					if (isset($GLOBALS['TL_HOOKS']['replaceInsertTags']) && \is_array($GLOBALS['TL_HOOKS']['replaceInsertTags']))
					{
						foreach ($GLOBALS['TL_HOOKS']['replaceInsertTags'] as $callback)
						{
							$this->import($callback[0]);
							$varValue = $this->{$callback[0]}->{$callback[1]}($tag, $blnCache, $arrCache[$strTag], $flags, $tags, $arrCache, $_rit, $_cnt); // see #6672

							// Replace the tag and stop the loop
							if ($varValue !== false)
							{
								$arrCache[$strTag] = $varValue;
								break;
							}
						}
					}

					\System::getContainer()
						->get('monolog.logger.contao')
						->log(LogLevel::INFO, 'Unknown insert tag: ' . $strTag)
					;
					break;
			}

			// Handle the flags
			if (!empty($flags))
			{
				foreach ($flags as $flag)
				{
					switch ($flag)
					{
						case 'addslashes':
						case 'standardize':
						case 'ampersand':
						case 'specialchars':
						case 'nl2br':
						case 'nl2br_pre':
						case 'strtolower':
						case 'utf8_strtolower':
						case 'strtoupper':
						case 'utf8_strtoupper':
						case 'ucfirst':
						case 'lcfirst':
						case 'ucwords':
						case 'trim':
						case 'rtrim':
						case 'ltrim':
						case 'utf8_romanize':
						case 'urlencode':
						case 'rawurlencode':
							$arrCache[$strTag] = $flag($arrCache[$strTag]);
							break;

						case 'encodeEmail':
							$arrCache[$strTag] = \StringUtil::$flag($arrCache[$strTag]);
							break;

						case 'number_format':
							$arrCache[$strTag] = \System::getFormattedNumber($arrCache[$strTag], 0);
							break;

						case 'currency_format':
							$arrCache[$strTag] = \System::getFormattedNumber($arrCache[$strTag], 2);
							break;

						case 'readable_size':
							$arrCache[$strTag] = \System::getReadableSize($arrCache[$strTag]);
							break;

						case 'flatten':
							if (!\is_array($arrCache[$strTag]))
							{
								break;
							}

							$it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arrCache[$strTag]));
							$result = array();

							foreach ($it as $leafValue)
							{
								$keys = array();

								foreach (range(0, $it->getDepth()) as $depth)
								{
									$keys[] = $it->getSubIterator($depth)->key();
								}

								$result[] = implode('.', $keys) . ': ' . $leafValue;
							}

							$arrCache[$strTag] = implode(', ', $result);
							break;

						// HOOK: pass unknown flags to callback functions
						default:
							if (isset($GLOBALS['TL_HOOKS']['insertTagFlags']) && \is_array($GLOBALS['TL_HOOKS']['insertTagFlags']))
							{
								foreach ($GLOBALS['TL_HOOKS']['insertTagFlags'] as $callback)
								{
									$this->import($callback[0]);
									$varValue = $this->{$callback[0]}->{$callback[1]}($flag, $tag, $arrCache[$strTag], $flags, $blnCache, $tags, $arrCache, $_rit, $_cnt); // see #5806

									// Replace the tag and stop the loop
									if ($varValue !== false)
									{
										$arrCache[$strTag] = $varValue;
										break;
									}
								}
							}

							\System::getContainer()
								->get('monolog.logger.contao')
								->log(LogLevel::INFO, 'Unknown insert tag flag: ' . $flag)
							;
							break;
					}
				}
			}

			$strBuffer .= $arrCache[$strTag];
		}

		return \StringUtil::restoreBasicEntities($strBuffer);
	}
}
