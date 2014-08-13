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
 * Reads and writes articles
 *
 * @property int    $id          the ID
 * @property int    $pid         the parent ID
 * @property int    $sorting     the sorting position
 * @property int    $tstamp      the modification date
 * @property string $title       the article title
 * @property string $alias       the article alias
 * @property int    $author      the ID of the author of the article
 * @property string $inColumn    the column to display the article in
 * @property string $keywords    an optional list of keywords
 * @property bool   $showTeaser  true if the teaser shall be displayed
 * @property string $teaserCssID the CSS ID and/or class of the teaser
 * @property string $teaser      the teaser text
 * @property bool   $printable   true if the article is printable
 * @property string $customTpl   an optional custom template
 * @property bool   $protected   true if the article is protected
 * @property array  $groups      an array of allowed groups
 * @property bool   $guests      true to show the article to guests only
 * @property string $cssID       the CSS ID and/or class of the article
 * @property array  $space       an optional space before and after the article
 * @property bool   $published   true if the article has been published
 * @property int    $start       an optional start date
 * @property int    $stop        an optional end date
 *
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class ArticleModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_article';


	/**
	 * Find an article by its ID or alias and its page
	 *
	 * @param mixed $varId      The numeric ID or alias name
	 * @param int   $intPid     The page ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return self|null The model or null if there is no article
	 */
	public static function findByIdOrAliasAndPid($varId, $intPid, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["($t.id=? OR $t.alias=?)"];
		$arrValues = [(is_numeric($varId) ? $varId : 0), $varId];

		if ($intPid)
		{
			$arrColumns[] = "$t.pid=?";
			$arrValues[] = $intPid;
		}

		return static::findOneBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find a published article by its ID
	 *
	 * @param int   $intId      The article ID
	 * @param array $arrOptions An optional options array
	 *
	 * @return self|null The model or null if there is no published article
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
	 * Find all published articles by their parent ID and column
	 *
	 * @param int    $intPid     The page ID
	 * @param string $strColumn  The column name
	 * @param array  $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedByPidAndColumn($intPid, $strColumn, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.inColumn=?"];
		$arrValues = [$intPid, $strColumn];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}


	/**
	 * Find all published articles with teaser by their parent ID and column
	 *
	 * @param int    $intPid     The page ID
	 * @param string $strColumn  The column name
	 * @param array  $arrOptions An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no articles in the given column
	 */
	public static function findPublishedWithTeaserByPidAndColumn($intPid, $strColumn, array $arrOptions=[])
	{
		$t = static::$strTable;
		$arrColumns = ["$t.pid=? AND $t.inColumn=? AND $t.showTeaser=1"];
		$arrValues = [$intPid, $strColumn];

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.published=1";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, $arrValues, $arrOptions);
	}
}
