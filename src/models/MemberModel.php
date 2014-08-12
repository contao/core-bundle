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


/**
 * Reads and writes members
 *
 * @method static findById()
 * @method static findByTstamp()
 * @method static findByFirstname()
 * @method static findByLastname()
 * @method static findByDateOfBirth()
 * @method static findByGender()
 * @method static findByCompany()
 * @method static findByStreet()
 * @method static findByPostal()
 * @method static findByCity()
 * @method static findByState()
 * @method static findByCountry()
 * @method static findByPhone()
 * @method static findByMobile()
 * @method static findByFax()
 * @method static findByEmail()
 * @method static findByWebsite()
 * @method static findByLanguage()
 * @method static findByLogin()
 * @method static findByUsername()
 * @method static findByHomeDir()
 * @method static findByDisable()
 * @method static findByStart()
 * @method static findByStop()
 * @method static findByDateAdded()
 * @method static findByLastLogin()
 * @method static findByCreatedOn()
 *
 * @package   Models
 * @author    Leo Feyer <https://github.com/leofeyer>
 * @copyright Leo Feyer 2005-2014
 */
class MemberModel extends Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_member';


	/**
	 * Find an active member by his/her e-mail-address and username
	 *
	 * @param string $strEmail    The e-mail address
	 * @param string $strUsername The username
	 * @param array  $arrOptions  An optional options array
	 *
	 * @return Model|null The model or null if there is no member
	 */
	public static function findActiveByEmailAndUsername($strEmail, $strUsername=null, array $arrOptions=[])
	{
		$time = time();
		$t = static::$strTable;

		$arrColumns = ["$t.email=? AND $t.login=1 AND ($t.start='' OR $t.start<$time) AND ($t.stop='' OR $t.stop>$time) AND $t.disable=''"];

		if ($strUsername !== null)
		{
			$arrColumns[] = "$t.username=?";
		}

		return static::findOneBy($arrColumns, [$strEmail, $strUsername], $arrOptions);
	}
}
