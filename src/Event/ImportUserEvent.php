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

use Symfony\Component\EventDispatcher\Event;

class ImportUserEvent extends Event
{
    public const NAME = 'contao.importUser';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    private $vote = false;

    /**
     * @param string $username
     * @param string $credentials
     * @param string $table
     */
    public function __construct(string $username, string $credentials, string $table)
    {
        $this->username = $username;
        $this->credentials = $credentials;
        $this->table = $table;
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
     * Returns the user table.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
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
