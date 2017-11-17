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

    public function __construct(string $username, string $credentials, string $table)
    {
        $this->username = $username;
        $this->credentials = $credentials;
        $this->table = $table;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getCredentials(): string
    {
        return $this->credentials;
    }

    public function getTable(): string
    {
        return $this->table;
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
