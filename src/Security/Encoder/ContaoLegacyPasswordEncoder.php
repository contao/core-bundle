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

@trigger_error('Using the ContaoLegacyPasswordEncoder has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

/**
 * Legacy password encoder for old sha1-encoded passwords.
 *
 * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
 */
class ContaoLegacyPasswordEncoder extends BasePasswordEncoder
{
    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    public function encodePassword($raw, $salt): string
    {
        @trigger_error('Using ContaoLegacyPasswordEncoder::encodePassword has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Password too long.');
        }

        return sha1($salt.$raw);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Deprecated since Contao 4.x, to be removed in Contao 5.0.
     */
    public function isPasswordValid($encoded, $raw, $salt): bool
    {
        @trigger_error('Using ContaoLegacyPasswordEncoder::isPasswordValid has been deprecated and will no longer work in Contao 5.0.', E_USER_DEPRECATED);

        return
            !$this->isPasswordTooLong($raw) &&
            $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }
}
