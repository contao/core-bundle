<?php

namespace Contao\CoreBundle\Framework;

use Contao\Config;
use Contao\Encryption;

trait ContaoUserAwareTrait
{
    protected $id;
    protected $username;
    protected $password;
    protected $salt;
    protected $encoder = false;
    protected $disable;

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
    abstract public function getRoles();

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
