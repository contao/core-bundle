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
 * Reads and writes style sheets
 *
 * The class reads from and writes to the style sheet table. It does not create
 * .css files on the hard disk. This is done by the StyleSheet class.
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $name
 * @property boolean $disablePie
 * @property integer $embedImages
 * @property string  $cc
 * @property string  $media
 * @property string  $mediaQuery
 * @property string  $vars
 * @property string  $type
 * @property boolean $hasFontFace
 * @property string  $singleSRC
 * @property integer $tstamp2
 * @property integer $tstamp3
 *
 * @method static StyleSheetModel|null findById($id, array $opt=array())
 * @method static StyleSheetModel|null findByPk($id, array $opt=array())
 * @method static StyleSheetModel|null findByIdOrAlias($val, array $opt=array())
 * @method static StyleSheetModel|null findOneBy($col, $val, array $opt=array())
 * @method static StyleSheetModel|null findByName($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByPid($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByTstamp($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByDisablePie($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByEmbedImages($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByCc($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByMedia($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByMediaQuery($val, array $opt=array())
 * @method static StyleSheetModel|null findOneByVars($val, array $opt=array())
 *
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByPid($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByDisablePie($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByEmbedImages($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByCc($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByMedia($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByMediaQuery($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findByVars($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|StyleSheetModel[]|StyleSheetModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByDisablePie($val, array $opt=array())
 * @method static integer countByEmbedImages($val, array $opt=array())
 * @method static integer countByCc($val, array $opt=array())
 * @method static integer countByMedia($val, array $opt=array())
 * @method static integer countByMediaQuery($val, array $opt=array())
 * @method static integer countByVars($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class StyleSheetModel extends \Model
{
	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_style_sheet';

	/**
	 * Find multiple style sheets by their IDs
	 *
	 * @param array $arrIds An array of style sheet IDs
	 *
	 * @return Model\Collection|StyleSheetModel[]|StyleSheetModel|null A collection of models or null if there are no style sheets
	 */
	public static function findByIds($arrIds)
	{
		if (empty($arrIds) || !\is_array($arrIds))
		{
			return null;
		}

		$objDatabase = \Database::getInstance();
		$arrIds = array_map('\intval', $arrIds);

		$objResult = $objDatabase->execute("SELECT *, (SELECT tstamp FROM tl_theme WHERE tl_theme.id=tl_style_sheet.pid) AS tstamp3, (SELECT MAX(tstamp) FROM tl_style WHERE tl_style.pid=tl_style_sheet.id) AS tstamp2, (SELECT COUNT(*) FROM tl_style WHERE tl_style.selector='@font-face' AND tl_style.invisible='' AND tl_style.pid=tl_style_sheet.id) AS hasFontFace FROM tl_style_sheet WHERE id IN (" . implode(',', $arrIds) . ") ORDER BY " . $objDatabase->findInSet('id', $arrIds));

		return static::createCollectionFromDbResult($objResult, 'tl_style_sheet');
	}
}
