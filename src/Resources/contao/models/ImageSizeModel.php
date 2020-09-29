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
 * Reads and writes image sizes
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $name
 * @property string  $cssClass
 * @property string  $sizes
 * @property string  $densities
 * @property integer $width
 * @property integer $height
 * @property string  $resizeMode
 * @property integer $zoom
 *
 * @method static ImageSizeModel|null findById($id, array $opt=array())
 * @method static ImageSizeModel|null findByPk($id, array $opt=array())
 * @method static ImageSizeModel|null findByIdOrAlias($val, array $opt=array())
 * @method static ImageSizeModel|null findOneBy($col, $val, array $opt=array())
 * @method static ImageSizeModel|null findOneByPid($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByTstamp($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByName($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByCssClass($val, array $opt=array())
 * @method static ImageSizeModel|null findOneBySizes($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByDensities($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByWidth($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByHeight($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByResizeMode($val, array $opt=array())
 * @method static ImageSizeModel|null findOneByZoom($val, array $opt=array())
 *
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByPid($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByName($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByCssClass($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findBySizes($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByDensities($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByWidth($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByHeight($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByResizeMode($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findByZoom($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|ImageSizeModel[]|ImageSizeModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByCssClass($val, array $opt=array())
 * @method static integer countBySizes($val, array $opt=array())
 * @method static integer countByDensities($val, array $opt=array())
 * @method static integer countByWidth($val, array $opt=array())
 * @method static integer countByHeight($val, array $opt=array())
 * @method static integer countByResizeMode($val, array $opt=array())
 * @method static integer countByZoom($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ImageSizeModel extends \Model
{
	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_image_size';
}
