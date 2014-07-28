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
 * @method static findById()          Find members by their ID
 * @method static findByTstamp()      Find members by their modification date
 * @method static findByFirstname()   Find members by their firstname
 * @method static findByLastname()    Find members by their lastname
 * @method static findByDateOfBirth() Find members by their date of birth
 * @method static findByGender()      Find members by their gender
 * @method static findByCompany()     Find members by their company name
 * @method static findByStreet()      Find members by their street
 * @method static findByPostal()      Find members by their postal code
 * @method static findByCity()        Find members by their city
 * @method static findByState()       Find members by their state
 * @method static findByCountry()     Find members by their country
 * @method static findByPhone()       Find members by their phone number
 * @method static findByMobile()      Find members by their mobile phone number
 * @method static findByFax()         Find members by their fax number
 * @method static findByEmail()       Find members by their e-mail address
 * @method static findByWebsite()     Find members by their website URL
 * @method static findByLanguage()    Find members by their language
 * @method static findByLogin()       Find members who are allowed to log in
 * @method static findByUsername()    Find members by their username
 * @method static findByHomeDir()     Find members by their home directory
 * @method static findByDisable()     Find members by their status
 * @method static findByStart()       Find members by their "activate on" date
 * @method static findByStop()        Find members by their "deactivate on" date
 * @method static findByDateAdded()   Find members by their date added
 * @method static findByLastLogin()   Find members by their last login date
 * @method static findByCreatedOn()   Find members by their "created on" date
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
