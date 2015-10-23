<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\MemberModel;
use Contao\Module;
use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a front end user sets a new password.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SetNewPasswordEvent extends Event
{
    /**
     * @var MemberModel
     */
    private $member;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Module
     */
    private $module;

    /**
     * Constructor.
     *
     * @param MemberModel $member   The member model
     * @param string      $password The password
     * @param Module|null $module   An optional module
     */
    public function __construct(MemberModel $member, $password, Module $module = null)
    {
        $this->member = $member;
        $this->password = $password;
        $this->module = $module;
    }

    /**
     * Returns the member model.
     *
     * @return MemberModel The member model
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Returns the password.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the module.
     *
     * @return Module The module
     */
    public function getModule()
    {
        return $this->module;
    }
}
