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
 * Reads and writes content elements
 *
 * @property int    $id               the ID
 * @property int    $pid              the parent ID
 * @property string $ptable           the parent table name
 * @property int    $sorting          the sorting position
 * @property int    $tstamp           the modification date
 * @property string $type             the element type
 * @property array  $headline         the headline and headline type
 * @property string $text             the HTML text
 * @property bool   $addImage         true if there is an image added
 * @property string $singleSRC        the file UUID
 * @property string $alt              the alternative image text
 * @property string $title            the image title
 * @property array  $size             the image size
 * @property array  $imagemargin      the image margin
 * @property string $imageUrl         an optional image link URL
 * @property bool   $fullsize         true if the image includes a fullsize link
 * @property string $caption          an optional image caption
 * @property string $floating         the image alignment
 * @property string $html             the HTML code
 * @property string $listtype         the type of list to render
 * @property array  $listitems        the list items
 * @property array  $tableitems       the table items
 * @property string $summary          an optional table summary
 * @property bool   $thead            true if the first row is the table header
 * @property bool   $tfoot            true if the last row is the table footer
 * @property bool   $tleft            true if the first column is the column header
 * @property bool   $sortable         true if the table is sortable
 * @property int    $sortIndex        the index of the default sorting column
 * @property int    $sortOrder        the default sorting order
 * @property string $mooHeadline      the accordion section headline
 * @property string $mooStyle         the accordion section style
 * @property array  $mooClasses       the accordion toggler and section classes
 * @property string $highlight        the syntax of the code snippet
 * @property string $shClass          an optional syntax highlighter CSS class
 * @property string $code             the code snippet to highlight
 * @property string $url              the link target
 * @property bool   $target           true if the link opens in a new browser window
 * @property string $titleText        the link title
 * @property string $linkTitle        the link text
 * @property string $embed            a text to embed the link in
 * @property string $rel              an optional rel attribute
 * @property bool   $useImage         true if the link shall be an image link
 * @property array  $multiSRC         a set of file UUIDs
 * @property array  $orderSRC         a set of file UUIDs
 * @property bool   $useHomeDir       true if the user's home directory shall be used
 * @property int    $perRow           the number of images per row in a gallery
 * @property int    $perPage          the number of images per page in a gallery
 * @property int    $numberOfItems    the total number of items
 * @property string $sortBy           the sorting order
 * @property string $galleryTpl       the gallery element template
 * @property string $customTpl        an optional custom template
 * @property string $playerSRC        a set of file UUIDs
 * @property string $youtube          a YouTube video ID
 * @property string $posterSRC        an optional poster image UUID
 * @property array  $playerSize       the video player's width and height
 * @property bool   $autoplay         true if the video shall start automatically
 * @property int    $sliderDelay      the slider delay
 * @property int    $sliderSpeed      the slider speed
 * @property int    $sliderStartSlide the index of the first slide
 * @property bool   $sliderContinuous true if the slider shall be continuous
 * @property int    $cteAlias         the ID of the alias element
 * @property int    $articleAlias     the ID of the alias article
 * @property int    $article          the article ID
 * @property int    $form             the form ID
 * @property int    $module           the module ID
 * @property bool   $protected        true if the element is protected
 * @property array  $groups           an array of allowed groups
 * @property bool   $guests           true to show the element to guests only
 * @property string $cssID            the CSS ID and/or class of the element
 * @property array  $space            an optional space before and after the element
 * @property bool   $invisible        true if the element is hidden
 * @property int    $start            an optional start date
 * @property int    $stop             an optional end date
 *
 * @method ContentModel current() return the current model

 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class ContentModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_content';


	/**
	 * Find all published content elements by their parent ID and parent table
	 *
	 * @param int    $intPid         The article ID
	 * @param string $strParentTable The parent table name
	 * @param array  $arrOptions     An optional options array
	 *
	 * @return Collection|null A collection of models or null if there are no content elements
	 */
	public static function findPublishedByPidAndTable($intPid, $strParentTable, array $arrOptions=[])
	{
		$t = static::$strTable;

		// Also handle empty ptable fields (backwards compatibility)
		if ($strParentTable == 'tl_article')
		{
			$arrColumns = ["$t.pid=? AND ($t.ptable=? OR $t.ptable='')"];
		}
		else
		{
			$arrColumns = ["$t.pid=? AND $t.ptable=?"];
		}

		if (!BE_USER_LOGGED_IN)
		{
			$time = time();
			$arrColumns[] = "($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.invisible=''";
		}

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = "$t.sorting";
		}

		return static::findBy($arrColumns, [$intPid, $strParentTable], $arrOptions);
	}
}
