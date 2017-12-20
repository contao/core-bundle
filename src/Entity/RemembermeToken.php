<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="rememberme_token", options={"contao"=true})
 *
 * @see \Symfony\Bridge\Doctrine\Security\RememberMe\DoctrineTokenProvider
 */
class RemembermeToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=88, unique=true, options={"fixed"=true})
     */
    private $series;

    /**
     * @ORM\Column(type="string", length=88, options={"fixed"=true})
     */
    private $value;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastUsed;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $class;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $username;
}
