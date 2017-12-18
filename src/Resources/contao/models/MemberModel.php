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
 * Reads and writes members
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $firstname
 * @property string  $lastname
 * @property string  $dateOfBirth
 * @property string  $gender
 * @property string  $company
 * @property string  $street
 * @property string  $postal
 * @property string  $city
 * @property string  $state
 * @property string  $country
 * @property string  $phone
 * @property string  $mobile
 * @property string  $fax
 * @property string  $email
 * @property string  $website
 * @property string  $language
 * @property string  $groups
 * @property boolean $login
 * @property string  $username
 * @property string  $password
 * @property boolean $assignDir
 * @property string  $homeDir
 * @property boolean $disable
 * @property string  $start
 * @property string  $stop
 * @property integer $dateAdded
 * @property integer $lastLogin
 * @property integer $currentLogin
 * @property integer $loginCount
 * @property integer $locked
 * @property string  $session
 * @property integer $createdOn
 * @property string  $activation
 * @property string  $newsletter
 *
 * @method static MemberModel|null findById($id, array $opt=array())
 * @method static MemberModel|null findByPk($id, array $opt=array())
 * @method static MemberModel|null findByIdOrAlias($val, array $opt=array())
 * @method static MemberModel|null findOneBy($col, $val, array $opt=array())
 * @method static MemberModel|null findByUsername($val, array $opt=array())
 * @method static MemberModel|null findOneByTstamp($val, array $opt=array())
 * @method static MemberModel|null findOneByFirstname($val, array $opt=array())
 * @method static MemberModel|null findOneByLastname($val, array $opt=array())
 * @method static MemberModel|null findOneByDateOfBirth($val, array $opt=array())
 * @method static MemberModel|null findOneByGender($val, array $opt=array())
 * @method static MemberModel|null findOneByCompany($val, array $opt=array())
 * @method static MemberModel|null findOneByStreet($val, array $opt=array())
 * @method static MemberModel|null findOneByPostal($val, array $opt=array())
 * @method static MemberModel|null findOneByCity($val, array $opt=array())
 * @method static MemberModel|null findOneByState($val, array $opt=array())
 * @method static MemberModel|null findOneByCountry($val, array $opt=array())
 * @method static MemberModel|null findOneByPhone($val, array $opt=array())
 * @method static MemberModel|null findOneByMobile($val, array $opt=array())
 * @method static MemberModel|null findOneByFax($val, array $opt=array())
 * @method static MemberModel|null findOneByEmail($val, array $opt=array())
 * @method static MemberModel|null findOneByWebsite($val, array $opt=array())
 * @method static MemberModel|null findOneByLanguage($val, array $opt=array())
 * @method static MemberModel|null findOneByGroups($val, array $opt=array())
 * @method static MemberModel|null findOneByLogin($val, array $opt=array())
 * @method static MemberModel|null findOneByPassword($val, array $opt=array())
 * @method static MemberModel|null findOneByAssignDir($val, array $opt=array())
 * @method static MemberModel|null findOneByHomeDir($val, array $opt=array())
 * @method static MemberModel|null findOneByDisable($val, array $opt=array())
 * @method static MemberModel|null findOneByStart($val, array $opt=array())
 * @method static MemberModel|null findOneByStop($val, array $opt=array())
 * @method static MemberModel|null findOneByDateAdded($val, array $opt=array())
 * @method static MemberModel|null findOneByLastLogin($val, array $opt=array())
 * @method static MemberModel|null findOneByCurrentLogin($val, array $opt=array())
 * @method static MemberModel|null findOneByLoginCount($val, array $opt=array())
 * @method static MemberModel|null findOneByLocked($val, array $opt=array())
 * @method static MemberModel|null findOneBySession($val, array $opt=array())
 * @method static MemberModel|null findOneByCreatedOn($val, array $opt=array())
 * @method static MemberModel|null findOneByActivation($val, array $opt=array())
 * @method static MemberModel|null findOneByNewsletter($val, array $opt=array())
 *
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByTstamp($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByFirstname($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLastname($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByDateOfBirth($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByGender($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByCompany($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByStreet($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByPostal($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByCity($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByState($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByCountry($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByPhone($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByMobile($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByFax($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByEmail($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByWebsite($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLanguage($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByGroups($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLogin($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByPassword($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByAssignDir($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByHomeDir($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByDisable($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByStart($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByStop($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByDateAdded($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLastLogin($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByCurrentLogin($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLoginCount($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByLocked($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findBySession($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByCreatedOn($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByActivation($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findByNewsletter($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findMultipleByIds($val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findBy($col, $val, array $opt=array())
 * @method static Model\Collection|MemberModel[]|MemberModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByFirstname($val, array $opt=array())
 * @method static integer countByLastname($val, array $opt=array())
 * @method static integer countByDateOfBirth($val, array $opt=array())
 * @method static integer countByGender($val, array $opt=array())
 * @method static integer countByCompany($val, array $opt=array())
 * @method static integer countByStreet($val, array $opt=array())
 * @method static integer countByPostal($val, array $opt=array())
 * @method static integer countByCity($val, array $opt=array())
 * @method static integer countByState($val, array $opt=array())
 * @method static integer countByCountry($val, array $opt=array())
 * @method static integer countByPhone($val, array $opt=array())
 * @method static integer countByMobile($val, array $opt=array())
 * @method static integer countByFax($val, array $opt=array())
 * @method static integer countByEmail($val, array $opt=array())
 * @method static integer countByWebsite($val, array $opt=array())
 * @method static integer countByLanguage($val, array $opt=array())
 * @method static integer countByGroups($val, array $opt=array())
 * @method static integer countByLogin($val, array $opt=array())
 * @method static integer countByUsername($val, array $opt=array())
 * @method static integer countByPassword($val, array $opt=array())
 * @method static integer countByAssignDir($val, array $opt=array())
 * @method static integer countByHomeDir($val, array $opt=array())
 * @method static integer countByDisable($val, array $opt=array())
 * @method static integer countByStart($val, array $opt=array())
 * @method static integer countByStop($val, array $opt=array())
 * @method static integer countByDateAdded($val, array $opt=array())
 * @method static integer countByLastLogin($val, array $opt=array())
 * @method static integer countByCurrentLogin($val, array $opt=array())
 * @method static integer countByLoginCount($val, array $opt=array())
 * @method static integer countByLocked($val, array $opt=array())
 * @method static integer countBySession($val, array $opt=array())
 * @method static integer countByCreatedOn($val, array $opt=array())
 * @method static integer countByActivation($val, array $opt=array())
 * @method static integer countByNewsletter($val, array $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class MemberModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_member';


	/**
	 * Find an active member by their e-mail-address and username
	 *
	 * @param string $strEmail    The e-mail address
	 * @param string $strUsername The username
	 * @param array  $arrOptions  An optional options array
	 *
	 * @return MemberModel|null The model or null if there is no member
	 */
	public static function findActiveByEmailAndUsername($strEmail, $strUsername=null, array $arrOptions=array())
	{
		$t = static::$strTable;
		$time = \Date::floorToMinute();

		$arrColumns = array("$t.email=? AND $t.login='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.disable=''");

		if ($strUsername !== null)
		{
			$arrColumns[] = "$t.username=?";
		}

		return static::findOneBy($arrColumns, array($strEmail, $strUsername), $arrOptions);
	}


	/**
	 * Find an unactivated member by their e-mail-address
	 *
	 * @param string $strEmail   The e-mail address
	 * @param array  $arrOptions An optional options array
	 *
	 * @return static The model or null if there is no member
	 */
	public static function findUnactivatedByEmail($strEmail, array $arrOptions=array())
	{
		$t = static::$strTable;

		return static::findOneBy(array("$t.email=? AND $t.activation!=''"), $strEmail, $arrOptions);
	}
}
