<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * Legacy password encoder for old sha1-encoded passwords.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author David Greminger <https://github.com/bytehead>
 */
class LegacyPasswordEncoder extends BasePasswordEncoder
{
    /**
     * @inheritDoc
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }

        return sha1($salt.$raw);
    }

    /**
     * @inheritDoc
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return
            !$this->isPasswordTooLong($raw) &&
            $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }

}
