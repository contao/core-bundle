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

use Contao\Model\Collection;


/**
 * Reads and writes pages
 *
 * @property int    $id              the ID
 * @property int    $pid             the parent ID
 * @property int    $sorting         the sorting position
 * @property int    $tstamp          the modification date
 * @property string $title           the page title
 * @property string $alias           the page alias
 * @property string $type            the page type
 * @property string $pageTitle       the meta title content
 * @property string $language        the page language
 * @property string $robots          the meta robots content
 * @property string $description     the page description
 * @property string $redirect        an optional redirect status code
 * @property int    $jumpTo          an optional forward page ID
 * @property string $url             an optional redirect URL
 * @property bool   $target          true if the page opens in a new browser window
 * @property string $dns             an optional domain name
 * @property string $staticFiles     an optional static URL for files
 * @property string $staticPlugins   an optional static URL for plugins
 * @property bool   $fallback        true if the page is the language fallback
 * @property string $adminEmail      an optional administrator e-mail address
 * @property string $dateFormat      an optional date format
 * @property string $timeFormat      an optional time format
 * @property string $datimFormat     an optional date and time format
 * @property bool   $createSitemap   true if a sitemap file is created
 * @property string $sitemapName     the name of the sitemap file
 * @property bool   $useSSL          true if the front end uses SSL
 * @property bool   $autoforward     true if visitors shall be forwarded automatically
 * @property bool   $protected       true if the page is protected
 * @property array  $groups          an array of allowed groups
 * @property bool   $includeLayout   true if the page has a layout assigned
 * @property int    $layout          the ID of the page layout
 * @property int    $mobileLayout    the ID of the mobile page layout
 * @property bool   $includeCache    true if the page is being cached
 * @property int    $cache           the cache timeout value
 * @property bool   $includeChmod    true if the page has permissions assigned
 * @property int    $cuser           the ID of the page owner
 * @property int    $cgroup          the ID of the page owner group
 * @property array  $chmod           the page permission array
 * @property bool   $noSearch        true if the page is exempt from being indexed
 * @property string $cssClass        the CSS class
 * @property string $sitemap         the sitemap status
 * @property bool   $hide            true if the page is hidden in the navigation menu
 * @property bool   $guests          true if the page is shown to guests only
 * @property int    $tabindex        an optional tab index
 * @property string $accesskey       an optional access key
 * @property bool   $published       true if the page has been published
 * @property int    $start           an optional start date
 * @property int    $stop            an optional end date
 * @property string $mainAlias       the alias of the main page
 * @property string $mainTitle       the title of the main page
 * @property string $mainPageTitle   the meta title content of the main page
 * @property string $parentAlias     the alias of the parent page
 * @property string $parentTitle     the title of the parent page
 * @property string $parentPageTitle the meta title content of the parent page
 * @property string $folderUrl       the folder URL prefix
 * @property int    $rootId          the ID of the root page
 * @property string $rootAlias       the alias of the root page
 * @property string $rootTitle       the title of the root page
 * @property string $rootPageTitle   the meta title content of the root page
 * @property string $domain          the domain name
 * @property string $rootLanguage    the language of the root page
 * @property bool   $rootIsPublic    the publication status of the root page
 * @property bool   $rootIsFallback  true if the root page is the language fallback
 * @property bool   $rootUseSSL      true if the root page uses SSL
 * @property array  $trail           an array of page IDs (current to root)
 *
 * @method PageModel current() return the current model
 *
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class PageModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_page';

	/**
	 * Details loaded
	 * @var bool
	 */
	protected $blnDetailsLoaded = false;


	/**
	 * Find a published page by its ID
	 *
	 * @param int   $intId      The page ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no published page
	 */
	public static function findPublishedById($intId, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.id=?"];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		return static::findOneBy($arrColumns, $intId, $arrOptions);
	}


	/**
	 * Find the first published root page by its host name and language
	 *
	 * @param string $strHost     The host name
	 * @param mixed  $varLanguage An ISO language code or an array of ISO language codes
	 * @param array  $arrOptions  An optional options array
	 *
	 * @return static The model or null if there is no matching root page
	 */
	public static function findFirstPublishedRootByHostAndLanguage($strHost, $varLanguage, array $arrOptions=[])
	{
		$t = static::$strTable;
		$objDatabase = Database::getInstance();

		if (is_array($varLanguage))
		{
			$arrColumns = ["$t.type='root' AND ($t.dns=? OR $t.dns='')"];

			if (!empty($varLanguage))
			{
				$arrColumns[] = "($t.language IN('". implode("','", $varLanguage) ."') OR $t.fallback=1)";
			}
			else
			{
				$arrColumns[] = "$t.fallback=1";
			}

			if (!isset($arrOptions['order']))
			{
				$arrOptions['order'] = "$t.dns DESC" . (!empty($varLanguage) ? ", " . $objDatabase->findInSet("$t.language", array_reverse($varLanguage)) . " DESC" : "") . ", $t.sorting";
			}

			if (!BE_USER_LOGGED_IN)
			{
				$time = time();
				$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
			}

			return static::findOneBy($arrColumns, $strHost, $arrOptions);
		}
		else
		{
			$arrColumns = ["$t.type='root' AND ($t.dns=? OR $t.dns='') AND ($t.language=? OR $t.fallback=1)"];
			$arrValues = [$strHost, $varLanguage];

			if (!isset($arrOptions['order']))
			{
				$arrOptions['order'] = "$t.dns DESC, $t.fallback";
			}

			if (!BE_USER_LOGGED_IN)
			{
				$time = time();
				$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
			}

			return static::findOneBy($arrColumns, $arrValues, $arrOptions);
		}
	}


	/**
	 * Find the first published page by its parent ID
	 *
	 * @param int   $intPid     The parent page's ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no published page
	 */
	public static function findFirstPublishedByPid($intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.type!='root' AND $t.type!='error_403' AND $t.type!='error_404'"];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findOneBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find the first published regular page by its parent ID
	 *
	 * @param int   $intPid The parent page's ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no published regular page
	 */
	public static function findFirstPublishedRegularByPid($intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.type='regular'"];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findOneBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find an error 403 page by its parent ID
	 *
	 * @param int   $intPid     The parent page's ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no 403 page
	 */
	public static function find403ByPid($intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.type='error_403'"];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findOneBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find an error 404 page by its parent ID
	 *
	 * @param int   $intPid     The parent page's ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no 404 page
	 */
	public static function find404ByPid($intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.type='error_404'"];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findOneBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find pages matching a list of possible alias names
	 *
	 * @param array $arrAliases An array of possible alias names
	 * @param array $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of Models or null if there is no matching pages
	 */
	public static function findByAliases($arrAliases, array $arrOptions=[])
	{
		if (!is_array($arrAliases) || empty($arrAliases))
		{
			return null;
		}

		// Remove everything that is not an alias
		$arrAliases = array_filter(array_map(function($v) {
			return preg_match('/^[\pN\pL\/\._-]+$/u', $v) ? $v : null;
		}, $arrAliases));

		// Return if nothing is left
		if (empty($arrAliases))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = ["$t.alias IN('" . implode("','", array_filter($arrAliases)) . "')"];

		// Check the publication status (see #4652)
		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = Database::getInstance()->findInSet("$t.alias", $arrAliases);
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}


	/**
	 * Find published pages by their ID or aliases
	 *
	 * @param mixed $varId      The numeric ID or the alias name
	 * @param array $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no pages
	 */
	public static function findPublishedByIdOrAlias($varId, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["($t.id=? OR $t.alias=?)"];
		$arrValues = [(is_numeric($varId) ? $varId : 0), $varId];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published subpages by their parent ID and exclude pages only visible for guests
	 *
	 * @param int  $intPid        The parent page's ID
	 * @param bool $blnShowHidden If true, hidden pages will be included
	 * @param bool $blnIsSitemap  If true, the sitemap settings apply
	 *
	 * @return Collection|null A collection of models or null if there are no pages
	 */
	public static function findPublishedSubpagesWithoutGuestsByPid($intPid, $blnShowHidden=false, $blnIsSitemap=false)
	{
		$time = time();

		$objSubpages = Database::getInstance()->prepare("SELECT p1.*, (SELECT COUNT(*) FROM tl_page p2 WHERE p2.pid=p1.id AND p2.type!='root' AND p2.type!='error_403' AND p2.type!='error_404'" . (!$blnShowHidden ? ($blnIsSitemap ? " AND (p2.hide='' OR sitemap='map_always')" : " AND p2.hide=''") : "") . ((FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN) ? " AND p2.guests=''" : "") . (!BE_USER_LOGGED_IN ? " AND (p2.start='' OR p2.start<$time) AND (p2.stop='' OR p2.stop>$time) AND p2.published=1" : "") . ") AS subpages FROM tl_page p1 WHERE p1.pid=? AND p1.type!='root' AND p1.type!='error_403' AND p1.type!='error_404'" . (!$blnShowHidden ? ($blnIsSitemap ? " AND (p1.hide='' OR sitemap='map_always')" : " AND p1.hide=''") : "") . ((FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN) ? " AND p1.guests=''" : "") . (!BE_USER_LOGGED_IN ? " AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time) AND p1.published=1" : "") . " ORDER BY p1.sorting")
											  ->execute($intPid);

		if ($objSubpages->numRows < 1)
		{
			return null;
		}

		return static::createCollectionFromDbResult($objSubpages, 'tl_page');
	}


	/**
	 * Find all published regular pages by their IDs and exclude pages only visible for guests
	 *
	 * @param int   $arrIds     An array of page IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no pages
	 */
	public static function findPublishedRegularWithoutGuestsByIds($arrIds, array $arrOptions=[])
	{
		if (!is_array($arrIds) || empty($arrIds))
		{
			return null;
		}

		$t = static::$strTable;
		$arrColumns = ["$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ") AND $t.type!='root' AND $t.type!='error_403' AND $t.type!='error_404'"];

		if (FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.guests=''";
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = Database::getInstance()->findInSet("$t.id", $arrIds);
		}

		return static::findBy($arrColumns, null, $arrOptions);
	}


	/**
	 * Find all published regular pages by their parent IDs and exclude pages only visible for guests
	 *
	 * @param int   $intPid     The parent page's ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no pages
	 */
	public static function findPublishedRegularWithoutGuestsByPid($intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.type!='root' AND $t.type!='error_403' AND $t.type!='error_404'"];

		if (FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN)
		{
			$arrColumns[] = "$t.guests=''";
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, $intPid, $arrOptions);
	}


	/**
	 * Find the parent pages of a page
	 *
	 * @param int $intId The page's ID
	 *
	 * @return Collection|null A collection of models or null if there are no parent pages
	 */
	public static function findParentsById($intId)
	{
		$arrModels = [];

		while ($intId > 0 && ($objPage = static::findByPk($intId)) !== null)
		{
			$intId = $objPage->pid;
			$arrModels[] = $objPage;
		}

		if (empty($arrModels))
		{
			return null;
		}

		return static::createCollection($arrModels, 'tl_page');
	}


	/**
	 * Find a page by its ID and return it with the inherited details
	 *
	 * @param int $intId The page's ID
	 *
	 * @return static The model or null if there is no matching page
	 */
	public static function findWithDetails($intId)
	{
		$objPage = static::findByPk($intId);

		if ($objPage === null)
		{
			return null;
		}

		return $objPage->loadDetails();
	}


	/**
	 * Get the details of a page including inherited parameters
	 *
	 * @return Model The page model
	 */
	public function loadDetails()
	{
		// Loaded already
		if ($this->blnDetailsLoaded)
		{
			return $this;
		}

		// Set some default values
		$this->protected = (bool) $this->protected;
		$this->groups = $this->protected ? deserialize($this->groups) : false;
		$this->layout = $this->includeLayout ? $this->layout : false;
		$this->mobileLayout = $this->includeLayout ? $this->mobileLayout : false;
		$this->cache = $this->includeCache ? $this->cache : false;

		$pid = $this->pid;
		$type = $this->type;
		$alias = $this->alias;
		$name = $this->title;
		$title = $this->pageTitle ?: $this->title;
		$folderUrl = '';
		$palias = '';
		$pname = '';
		$ptitle = '';
		$trail = [$this->id, $pid];

		// Inherit the settings
		if ($this->type == 'root')
		{
			$objParentPage = $this; // see #4610
		}
		else
		{
			// Load all parent pages
			$objParentPage = PageModel::findParentsById($pid);

			if ($objParentPage !== null)
			{
				while ($pid > 0 && $type != 'root' && $objParentPage->next())
				{
					$pid = $objParentPage->pid;
					$type = $objParentPage->type;

					// Parent title
					if ($ptitle == '')
					{
						$palias = $objParentPage->alias;
						$pname = $objParentPage->title;
						$ptitle = $objParentPage->pageTitle ?: $objParentPage->title;
					}

					// Page title
					if ($type != 'root')
					{
						$alias = $objParentPage->alias;
						$name = $objParentPage->title;
						$title = $objParentPage->pageTitle ?: $objParentPage->title;
						$folderUrl = basename($alias) . '/' . $folderUrl;
						$trail[] = $objParentPage->pid;
					}

					// Cache
					if ($objParentPage->includeCache && $this->cache === false)
					{
						$this->cache = $objParentPage->cache;
					}

					// Layout
					if ($objParentPage->includeLayout)
					{
						if ($this->layout === false)
						{
							$this->layout = $objParentPage->layout;
						}
						if ($this->mobileLayout === false)
						{
							$this->mobileLayout = $objParentPage->mobileLayout;
						}
					}

					// Protection
					if ($objParentPage->protected && $this->protected === false)
					{
						$this->protected = true;
						$this->groups = deserialize($objParentPage->groups);
					}
				}
			}

			// Set the titles
			$this->mainAlias = $alias;
			$this->mainTitle = $name;
			$this->mainPageTitle = $title;
			$this->parentAlias = $palias;
			$this->parentTitle = $pname;
			$this->parentPageTitle = $ptitle;
			$this->folderUrl = $folderUrl;
		}

		// Set the root ID and title
		if ($objParentPage !== null && $objParentPage->type == 'root')
		{
			$this->rootId = $objParentPage->id;
			$this->rootAlias = $objParentPage->alias;
			$this->rootTitle = $objParentPage->title;
			$this->rootPageTitle = $objParentPage->pageTitle ?: $objParentPage->title;
			$this->domain = $objParentPage->dns;
			$this->rootLanguage = $objParentPage->language;
			$this->language = $objParentPage->language;
			$this->staticFiles = $objParentPage->staticFiles;
			$this->staticPlugins = $objParentPage->staticPlugins;
			$this->dateFormat = $objParentPage->dateFormat;
			$this->timeFormat = $objParentPage->timeFormat;
			$this->datimFormat = $objParentPage->datimFormat;
			$this->adminEmail = $objParentPage->adminEmail;

			// Store whether the root page has been published
			$time = time();
			$this->rootIsPublic = ($objParentPage->published && ($objParentPage->start == '' || $objParentPage->start < $time) && ($objParentPage->stop == '' || $objParentPage->stop > $time));
			$this->rootIsFallback = ($objParentPage->fallback != '');
			$this->rootUseSSL = $objParentPage->useSSL;
		}

		// No root page found
		elseif (TL_MODE == 'FE' && $this->type != 'root')
		{
			header('HTTP/1.1 404 Not Found');
			System::log('Page ID "'. $this->id .'" does not belong to a root page', __METHOD__, TL_ERROR);
			die_nicely('be_no_root', 'No root page found');
		}

		$this->trail = array_reverse($trail);

		// Remove insert tags from all titles (see #2853)
		$this->title = strip_insert_tags($this->title);
		$this->pageTitle = strip_insert_tags($this->pageTitle);
		$this->parentTitle = strip_insert_tags($this->parentTitle);
		$this->parentPageTitle = strip_insert_tags($this->parentPageTitle);
		$this->mainTitle = strip_insert_tags($this->mainTitle);
		$this->mainPageTitle = strip_insert_tags($this->mainPageTitle);
		$this->rootTitle = strip_insert_tags($this->rootTitle);

		// Do not cache protected pages
		if ($this->protected)
		{
			$this->cache = 0;
		}

		// Use the global date format if none is set (see #6104)
		if ($this->dateFormat == '')
		{
			$this->dateFormat = Config::get('dateFormat');
		}
		if ($this->timeFormat == '')
		{
			$this->timeFormat = Config::get('timeFormat');
		}
		if ($this->datimFormat == '')
		{
			$this->datimFormat = Config::get('datimFormat');
		}

		// Prevent saving (see #6506 and #7199)
		$this->preventSaving();
		$this->blnDetailsLoaded = true;

		return $this;
	}


	/**
	 * Generate an URL depending on the current rewriteURL setting
	 *
	 * @param string $strParams    An optional string of URL parameters
	 * @param string $strForceLang Force a certain language
	 *
	 * @return string An URL that can be used in the front end
	 */
	public function getFrontendUrl($strParams=null, $strForceLang=null)
	{
		return Controller::generateFrontendUrl($this->row(), $strParams, $strForceLang);
	}
}
