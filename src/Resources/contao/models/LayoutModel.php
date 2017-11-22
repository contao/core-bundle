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
 * Reads and writes page layouts
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $name
 * @property string  $rows
 * @property string  $headerHeight
 * @property string  $footerHeight
 * @property string  $cols
 * @property string  $widthLeft
 * @property string  $widthRight
 * @property string  $sections
 * @property string  $framework
 * @property string  $stylesheet
 * @property string  $external
 * @property string  $orderExt
 * @property string  $loadingOrder
 * @property boolean $combineScripts
 * @property string  $modules
 * @property string  $template
 * @property string  $doctype
 * @property string  $webfonts
 * @property boolean $picturefill
 * @property string  $viewport
 * @property string  $titleTag
 * @property string  $cssClass
 * @property string  $onload
 * @property string  $head
 * @property boolean $addJQuery
 * @property string  $jSource
 * @property string  $jquery
 * @property boolean $addMooTools
 * @property string  $mooSource
 * @property string  $mootools
 * @property string  $analytics
 * @property string  $externalJs
 * @property string  $orderExtJs
 * @property string  $script
 * @property string  $scripts
 * @property boolean $static
 * @property string  $width
 * @property string  $align
 *
 * @method static LayoutModel|null findById($id, array $opt=array())
 * @method static LayoutModel|null findByPk($id, array $opt=array())
 * @method static LayoutModel|null findByIdOrAlias($val, array $opt=array())
 * @method static LayoutModel|null findOneBy($col, $val, array $opt=array())
 * @method static LayoutModel|null findOneByPid($val, array $opt=array())
 * @method static LayoutModel|null findOneByTstamp($val, array $opt=array())
 * @method static LayoutModel|null findOneByName($val, array $opt=array())
 * @method static LayoutModel|null findOneByRows($val, array $opt=array())
 * @method static LayoutModel|null findOneByHeaderHeight($val, array $opt=array())
 * @method static LayoutModel|null findOneByFooterHeight($val, array $opt=array())
 * @method static LayoutModel|null findOneByCols($val, array $opt=array())
 * @method static LayoutModel|null findOneByWidthLeft($val, array $opt=array())
 * @method static LayoutModel|null findOneByWidthRight($val, array $opt=array())
 * @method static LayoutModel|null findOneBySections($val, array $opt=array())
 * @method static LayoutModel|null findOneByFramework($val, array $opt=array())
 * @method static LayoutModel|null findOneByStylesheet($val, array $opt=array())
 * @method static LayoutModel|null findOneByExternal($val, array $opt=array())
 * @method static LayoutModel|null findOneByOrderExt($val, array $opt=array())
 * @method static LayoutModel|null findOneByLoadingOrder($val, array $opt=array())
 * @method static LayoutModel|null findOneByCombineScripts($val, array $opt=array())
 * @method static LayoutModel|null findOneByModules($val, array $opt=array())
 * @method static LayoutModel|null findOneByTemplate($val, array $opt=array())
 * @method static LayoutModel|null findOneByDoctype($val, array $opt=array())
 * @method static LayoutModel|null findOneByWebfonts($val, array $opt=array())
 * @method static LayoutModel|null findOneByPicturefill($val, array $opt=array())
 * @method static LayoutModel|null findOneByViewport($val, array $opt=array())
 * @method static LayoutModel|null findOneByTitleTag($val, array $opt=array())
 * @method static LayoutModel|null findOneByCssClass($val, array $opt=array())
 * @method static LayoutModel|null findOneByOnload($val, array $opt=array())
 * @method static LayoutModel|null findOneByHead($val, array $opt=array())
 * @method static LayoutModel|null findOneByAddJQuery($val, array $opt=array())
 * @method static LayoutModel|null findOneByJSource($val, array $opt=array())
 * @method static LayoutModel|null findOneByJquery($val, array $opt=array())
 * @method static LayoutModel|null findOneByAddMooTools($val, array $opt=array())
 * @method static LayoutModel|null findOneByMooSource($val, array $opt=array())
 * @method static LayoutModel|null findOneByMootools($val, array $opt=array())
 * @method static LayoutModel|null findOneByAnalytics($val, array $opt=array())
 * @method static LayoutModel|null findOneByExternalJs($val, array $opt=array())
 * @method static LayoutModel|null findOneByOrderExtJs($val, array $opt=array())
 * @method static LayoutModel|null findOneByScript($val, array $opt=array())
 * @method static LayoutModel|null findOneByScripts($val, array $opt=array())
 * @method static LayoutModel|null findOneByStatic($val, array $opt=array())
 * @method static LayoutModel|null findOneByWidth($val, array $opt=array())
 * @method static LayoutModel|null findOneByAlign($val, array $opt=array())
 *
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByPid($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByName($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByRows($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByHeaderHeight($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByFooterHeight($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByCols($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByWidthLeft($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByWidthRight($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findBySections($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByFramework($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByStylesheet($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByExternal($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByOrderExt($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByLoadingOrder($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByCombineScripts($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByModules($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByTemplate($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByDoctype($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByWebfonts($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByPicturefill($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByViewport($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByTitleTag($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByCssClass($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByOnload($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByHead($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByAddJQuery($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByJSource($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByJquery($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByAddMooTools($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByMooSource($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByMootools($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByAnalytics($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByExternalJs($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByOrderExtJs($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByScript($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByScripts($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByStatic($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByWidth($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findByAlign($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|LayoutModel[]|LayoutModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByRows($val, array $opt=array())
 * @method static integer countByHeaderHeight($val, array $opt=array())
 * @method static integer countByFooterHeight($val, array $opt=array())
 * @method static integer countByCols($val, array $opt=array())
 * @method static integer countByWidthLeft($val, array $opt=array())
 * @method static integer countByWidthRight($val, array $opt=array())
 * @method static integer countBySections($val, array $opt=array())
 * @method static integer countByFramework($val, array $opt=array())
 * @method static integer countByStylesheet($val, array $opt=array())
 * @method static integer countByExternal($val, array $opt=array())
 * @method static integer countByOrderExt($val, array $opt=array())
 * @method static integer countByLoadingOrder($val, array $opt=array())
 * @method static integer countByCombineScripts($val, array $opt=array())
 * @method static integer countByModules($val, array $opt=array())
 * @method static integer countByTemplate($val, array $opt=array())
 * @method static integer countByDoctype($val, array $opt=array())
 * @method static integer countByWebfonts($val, array $opt=array())
 * @method static integer countByPicturefill($val, array $opt=array())
 * @method static integer countByViewport($val, array $opt=array())
 * @method static integer countByTitleTag($val, array $opt=array())
 * @method static integer countByCssClass($val, array $opt=array())
 * @method static integer countByOnload($val, array $opt=array())
 * @method static integer countByHead($val, array $opt=array())
 * @method static integer countByAddJQuery($val, array $opt=array())
 * @method static integer countByJSource($val, array $opt=array())
 * @method static integer countByJquery($val, array $opt=array())
 * @method static integer countByAddMooTools($val, array $opt=array())
 * @method static integer countByMooSource($val, array $opt=array())
 * @method static integer countByMootools($val, array $opt=array())
 * @method static integer countByAnalytics($val, array $opt=array())
 * @method static integer countByExternalJs($val, array $opt=array())
 * @method static integer countByOrderExtJs($val, array $opt=array())
 * @method static integer countByScript($val, array $opt=array())
 * @method static integer countByScripts($val, array $opt=array())
 * @method static integer countByStatic($val, array $opt=array())
 * @method static integer countByWidth($val, array $opt=array())
 * @method static integer countByAlign($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class LayoutModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_layout';

}
