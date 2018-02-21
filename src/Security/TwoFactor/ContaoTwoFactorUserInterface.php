<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\TwoFactor;

interface ContaoTwoFactorUserInterface
{
    /**
     * @return string|null
     */
    public function getSecret(): ?string;

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void;
}
