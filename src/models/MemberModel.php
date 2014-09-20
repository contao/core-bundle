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
 * @method static findById()          find members by their ID
 * @method static findByTstamp()      find members by their modification date
 * @method static findByFirstname()   find members by their firstname
 * @method static findByLastname()    find members by their lastname
 * @method static findByDateOfBirth() find members by their date of birth
 * @method static findByGender()      find members by their gender
 * @method static findByCompany()     find members by their company name
 * @method static findByStreet()      find members by their street
 * @method static findByPostal()      find members by their postal code
 * @method static findByCity()        find members by their city
 * @method static findByState()       find members by their state
 * @method static findByCountry()     find members by their country
 * @method static findByPhone()       find members by their phone number
 * @method static findByMobile()      find members by their mobile phone number
 * @method static findByFax()         find members by their fax number
 * @method static findByEmail()       find members by their e-mail address
 * @method static findByWebsite()     find members by their website URL
 * @method static findByLanguage()    find members by their language
 * @method static findByLogin()       find members who are allowed to log in
 * @method static findByUsername()    find members by their username
 * @method static findByHomeDir()     find members by their home directory
 * @method static findByDisable()     find members by their activation status
 * @method static findByStart()       find members by their activation date
 * @method static findByStop()        find members by their deactivation date
 * @method static findByDateAdded()   find members by their date added
 * @method static findByLastLogin()   find members by their last login date
 * @method static findByCreatedOn()   find members by their creation date
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
