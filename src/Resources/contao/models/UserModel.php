<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;

use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;


/**
 * Reads and writes users
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $username
 * @property string  $name
 * @property string  $email
 * @property string  $language
 * @property string  $backendTheme
 * @property string  $uploader
 * @property boolean $showHelp
 * @property boolean $thumbnails
 * @property boolean $useRTE
 * @property boolean $useCE
 * @property string  $password
 * @property boolean $pwChange
 * @property boolean $admin
 * @property string  $groups
 * @property string  $inherit
 * @property string  $modules
 * @property string  $themes
 * @property string  $pagemounts
 * @property string  $alpty
 * @property string  $filemounts
 * @property string  $fop
 * @property string  $forms
 * @property string  $formp
 * @property boolean $disable
 * @property string  $start
 * @property string  $stop
 * @property string  $session
 * @property integer $dateAdded
 * @property integer $lastLogin
 * @property integer $currentLogin
 * @property integer $loginCount
 * @property integer $locked
 *
 * @method static UserModel|null findById($id, $opt=array())
 * @method static UserModel|null findByPk($id, $opt=array())
 * @method static UserModel|null findByIdOrAlias($val, $opt=array())
 * @method static UserModel|null findByUsername($val, $opt=array())
 * @method static UserModel|null findOneBy($col, $val, $opt=array())
 * @method static UserModel|null findOneByTstamp($val, $opt=array())
 * @method static UserModel|null findOneByName($val, $opt=array())
 * @method static UserModel|null findOneByEmail($val, $opt=array())
 * @method static UserModel|null findOneByLanguage($val, $opt=array())
 * @method static UserModel|null findOneByBackendTheme($val, $opt=array())
 * @method static UserModel|null findOneByUploader($val, $opt=array())
 * @method static UserModel|null findOneByShowHelp($val, $opt=array())
 * @method static UserModel|null findOneByThumbnails($val, $opt=array())
 * @method static UserModel|null findOneByUseRTE($val, $opt=array())
 * @method static UserModel|null findOneByUseCE($val, $opt=array())
 * @method static UserModel|null findOneByPassword($val, $opt=array())
 * @method static UserModel|null findOneByPwChange($val, $opt=array())
 * @method static UserModel|null findOneByAdmin($val, $opt=array())
 * @method static UserModel|null findOneByGroups($val, $opt=array())
 * @method static UserModel|null findOneByInherit($val, $opt=array())
 * @method static UserModel|null findOneByModules($val, $opt=array())
 * @method static UserModel|null findOneByThemes($val, $opt=array())
 * @method static UserModel|null findOneByPagemounts($val, $opt=array())
 * @method static UserModel|null findOneByAlpty($val, $opt=array())
 * @method static UserModel|null findOneByFilemounts($val, $opt=array())
 * @method static UserModel|null findOneByFop($val, $opt=array())
 * @method static UserModel|null findOneByForms($val, $opt=array())
 * @method static UserModel|null findOneByFormp($val, $opt=array())
 * @method static UserModel|null findOneByDisable($val, $opt=array())
 * @method static UserModel|null findOneByStart($val, $opt=array())
 * @method static UserModel|null findOneByStop($val, $opt=array())
 * @method static UserModel|null findOneBySession($val, $opt=array())
 * @method static UserModel|null findOneByDateAdded($val, $opt=array())
 * @method static UserModel|null findOneByLastLogin($val, $opt=array())
 * @method static UserModel|null findOneByCurrentLogin($val, $opt=array())
 * @method static UserModel|null findOneByLoginCount($val, $opt=array())
 * @method static UserModel|null findOneByLocked($val, $opt=array())
 *
 * @method static Model\Collection|UserModel[]|UserModel|null findByTstamp($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByName($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByEmail($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByLanguage($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByBackendTheme($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByUploader($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByShowHelp($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByThumbnails($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByUseRTE($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByUseCE($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByPassword($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByPwChange($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByAdmin($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByGroups($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByInherit($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByModules($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByThemes($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByPagemounts($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByAlpty($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByFilemounts($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByFop($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByForms($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByFormp($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByDisable($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByStart($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByStop($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findBySession($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByDateAdded($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByLastLogin($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByCurrentLogin($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByLoginCount($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findByLocked($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findMultipleByIds($val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findBy($col, $val, $opt=array())
 * @method static Model\Collection|UserModel[]|UserModel|null findAll($opt=array())
 *
 * @method static integer countById($id, $opt=array())
 * @method static integer countByTstamp($val, $opt=array())
 * @method static integer countByUsername($val, $opt=array())
 * @method static integer countByName($val, $opt=array())
 * @method static integer countByEmail($val, $opt=array())
 * @method static integer countByLanguage($val, $opt=array())
 * @method static integer countByBackendTheme($val, $opt=array())
 * @method static integer countByUploader($val, $opt=array())
 * @method static integer countByShowHelp($val, $opt=array())
 * @method static integer countByThumbnails($val, $opt=array())
 * @method static integer countByUseRTE($val, $opt=array())
 * @method static integer countByUseCE($val, $opt=array())
 * @method static integer countByPassword($val, $opt=array())
 * @method static integer countByPwChange($val, $opt=array())
 * @method static integer countByAdmin($val, $opt=array())
 * @method static integer countByGroups($val, $opt=array())
 * @method static integer countByInherit($val, $opt=array())
 * @method static integer countByModules($val, $opt=array())
 * @method static integer countByThemes($val, $opt=array())
 * @method static integer countByPagemounts($val, $opt=array())
 * @method static integer countByAlpty($val, $opt=array())
 * @method static integer countByFilemounts($val, $opt=array())
 * @method static integer countByFop($val, $opt=array())
 * @method static integer countByForms($val, $opt=array())
 * @method static integer countByFormp($val, $opt=array())
 * @method static integer countByDisable($val, $opt=array())
 * @method static integer countByStart($val, $opt=array())
 * @method static integer countByStop($val, $opt=array())
 * @method static integer countBySession($val, $opt=array())
 * @method static integer countByDateAdded($val, $opt=array())
 * @method static integer countByLastLogin($val, $opt=array())
 * @method static integer countByCurrentLogin($val, $opt=array())
 * @method static integer countByLoginCount($val, $opt=array())
 * @method static integer countByLocked($val, $opt=array())
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class UserModel extends Model implements AdvancedUserInterface, EncoderAwareInterface, \Serializable
{
    protected $id;
    protected $username;
    protected $password;
    protected $salt;
    protected $encoder = false;
    protected $disable;

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_user';

    /**
     * Get User ID
     *
     * @return int $id
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Set User ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        $this->getEncoderName();

        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        $this->getEncoderName();

        return $this->salt;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get encoder name
     *
     * @return string
     */
    public function getEncoder()
    {
        return $this->getEncoderName();
    }

    /**
     * Set encoder
     *
     * @param string $encoder
     * @return $this
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * Get disable flag
     *
     * @return bool
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * Set disable flag
     *
     * @param bool $disable
     * @return $this
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        if ($this->admin) {
            return [
                'ROLE_USER',
                'ROLE_ADMIN',
            ];
        }

        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonExpired()
    {
        $time = time();

        return ($this->start == '' || $this->start < $time) && ($this->stop == '' || $this->stop > $time);
    }

    /**
     * @inheritDoc
     */
    public function isAccountNonLocked()
    {
        $time = time();

        return ($this->locked + Config::get('lockPeriod')) < $time;
    }

    /**
     * @inheritDoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return !$this->disable;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            !$this->disable,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->disable
        ) = unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function getEncoderName()
    {
        if (false === $this->encoder) {
            $this->selectEncoder();
        }

        return $this->encoder;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Map $this->arrData values to real object properties
     * The Symfony authenticator needs real properties and their getter methods.
     */
    public function applyArrDataToProperties()
    {
        $this->setId($this->arrData['id']);
        $this->setUsername($this->arrData['username']);
        $this->setEncoder($this->getEncoderName());
        $this->setDisable($this->arrData['disable']);
    }

    /**
     * Selects a matching encoder based on actual password.
     */
    protected function selectEncoder()
    {
        if (false === $this->encoder) {
            if (Encryption::test($this->arrData['password'])) {
                $this->setEncoder('default');
                $this->setPassword($this->arrData['password']);
            } else {
                list($password, $salt) = explode(':', $this->arrData['password']);

                $this->setEncoder('legacy');
                $this->setPassword($password);
                $this->setSalt($salt);
            }
        }
    }
}
