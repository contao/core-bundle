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
 * Reads and writes user groups
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $name
 * @property string  $modules
 * @property string  $themes
 * @property string  $pagemounts
 * @property string  $alpty
 * @property string  $filemounts
 * @property string  $fop
 * @property string  $forms
 * @property string  $formp
 * @property string  $amg
 * @property string  $alexf
 * @property boolean $disable
 * @property string  $start
 * @property string  $stop
 *
 * @method static UserGroupModel|null findById($id, array $opt=array())
 * @method static UserGroupModel|null findByPk($id, array $opt=array())
 * @method static UserGroupModel|null findByIdOrAlias($val, array $opt=array())
 * @method static UserGroupModel|null findOneBy($col, $val, array $opt=array())
 * @method static UserGroupModel|null findOneByTstamp($val, array $opt=array())
 * @method static UserGroupModel|null findOneByName($val, array $opt=array())
 * @method static UserGroupModel|null findOneByModules($val, array $opt=array())
 * @method static UserGroupModel|null findOneByThemes($val, array $opt=array())
 * @method static UserGroupModel|null findOneByPagemounts($val, array $opt=array())
 * @method static UserGroupModel|null findOneByAlpty($val, array $opt=array())
 * @method static UserGroupModel|null findOneByFilemounts($val, array $opt=array())
 * @method static UserGroupModel|null findOneByFop($val, array $opt=array())
 * @method static UserGroupModel|null findOneByForms($val, array $opt=array())
 * @method static UserGroupModel|null findOneByFormp($val, array $opt=array())
 * @method static UserGroupModel|null findOneByAmg($val, array $opt=array())
 * @method static UserGroupModel|null findOneByAlexf($val, array $opt=array())
 * @method static UserGroupModel|null findOneByDisable($val, array $opt=array())
 * @method static UserGroupModel|null findOneByStart($val, array $opt=array())
 * @method static UserGroupModel|null findOneByStop($val, array $opt=array())
 *
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByName($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByModules($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByThemes($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByPagemounts($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByAlpty($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByFilemounts($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByFop($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByForms($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByFormp($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByAmg($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByAlexf($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByDisable($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByStart($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findByStop($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|UserGroupModel[]|UserGroupModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByModules($val, array $opt=array())
 * @method static integer countByThemes($val, array $opt=array())
 * @method static integer countByPagemounts($val, array $opt=array())
 * @method static integer countByAlpty($val, array $opt=array())
 * @method static integer countByFilemounts($val, array $opt=array())
 * @method static integer countByFop($val, array $opt=array())
 * @method static integer countByForms($val, array $opt=array())
 * @method static integer countByFormp($val, array $opt=array())
 * @method static integer countByAmg($val, array $opt=array())
 * @method static integer countByAlexf($val, array $opt=array())
 * @method static integer countByDisable($val, array $opt=array())
 * @method static integer countByStart($val, array $opt=array())
 * @method static integer countByStop($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class UserGroupModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_user_group';

}

class_alias(UserGroupModel::class, 'UserGroupModel');
