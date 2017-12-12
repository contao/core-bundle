<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Contao\User;
use Symfony\Component\EventDispatcher\Event;

class CheckCredentialsEvent extends Event
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $credentials;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $vote = false;

    /**
     * @param string $username
     * @param string $credentials
     * @param User   $user
     */
    public function __construct(string $username, string $credentials, User $user)
    {
        $this->username = $username;
        $this->credentials = $credentials;
        $this->user = $user;
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Returns the credentials.
     *
     * @return string
     */
    public function getCredentials(): string
    {
        return $this->credentials;
    }

    /**
     * Returns the user object.
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Gives a vote for the event.
     *
     * @param bool $vote
     */
    public function vote(bool $vote): void
    {
        $this->vote = $this->vote || $vote;
    }

    /**
     * Returns the voting result.
     *
     * @return bool
     */
    public function getVote(): bool
    {
        return $this->vote;
    }
}
