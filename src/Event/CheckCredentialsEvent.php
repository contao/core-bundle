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
    public const NAME = 'contao.checkCredentials';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $credentials;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var bool
     */
    private $vote = false;

    public function __construct(string $username, string $credentials, User $user)
    {
        $this->username = $username;
        $this->credentials = $credentials;
        $this->user = $user;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getCredentials(): string
    {
        return $this->credentials;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function vote(bool $vote): void
    {
        $this->vote = $this->vote || $vote;
    }

    public function getVote(): bool
    {
        return $this->vote;
    }
}
