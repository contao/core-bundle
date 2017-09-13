<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Legacy password encoder for old sha1-encoded passwords.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 */
class LegacyPasswordEncoder extends BasePasswordEncoder
{
    /**
     * {@inheritdoc}
     */
    public function encodePassword($raw, $salt): string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }

        return sha1($salt.$raw);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt): bool
    {
        return
            !$this->isPasswordTooLong($raw) &&
            $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }
}
