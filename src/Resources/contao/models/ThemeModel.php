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
 * Reads and writes themes
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $name
 * @property string  $author
 * @property string  $folders
 * @property string  $screenshot
 * @property string  $templates
 * @property string  $vars
 * @property string  $defaultImageDensities
 *
 * @method static ThemeModel|null findById($id, array $opt=array())
 * @method static ThemeModel|null findByPk($id, array $opt=array())
 * @method static ThemeModel|null findByIdOrAlias($val, array $opt=array())
 * @method static ThemeModel|null findOneBy($col, $val, array $opt=array())
 * @method static ThemeModel|null findOneByTstamp($val, array $opt=array())
 * @method static ThemeModel|null findOneByName($val, array $opt=array())
 * @method static ThemeModel|null findOneByAuthor($val, array $opt=array())
 * @method static ThemeModel|null findOneByFolders($val, array $opt=array())
 * @method static ThemeModel|null findOneByScreenshot($val, array $opt=array())
 * @method static ThemeModel|null findOneByTemplates($val, array $opt=array())
 * @method static ThemeModel|null findOneByVars($val, array $opt=array())
 * @method static ThemeModel|null findOneByDefaultImageDensities($val, array $opt=array())
 *
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByName($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByAuthor($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByFolders($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByScreenshot($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByTemplates($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByVars($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findByDefaultImageDensities($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|ThemeModel[]|ThemeModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByAuthor($val, array $opt=array())
 * @method static integer countByFolders($val, array $opt=array())
 * @method static integer countByScreenshot($val, array $opt=array())
 * @method static integer countByTemplates($val, array $opt=array())
 * @method static integer countByVars($val, array $opt=array())
 * @method static integer countByDefaultImageDensities($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ThemeModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_theme';

}
