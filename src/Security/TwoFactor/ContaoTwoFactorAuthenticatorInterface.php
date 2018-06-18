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

use Contao\User;
use Symfony\Component\HttpFoundation\Request;

interface ContaoTwoFactorAuthenticatorInterface
{
    /**
     * Validates the code, which was entered by the user.
     *
     * @param User   $user
     * @param string $code
     *
     * @return bool
     */
    public function validateCode(User $user, string $code): bool;

    /**
     * Generates the TOTP provision URI.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return string
     */
    public function getProvisionUri(User $user, Request $request): string;

    /**
     * Generates the QR code as SVG and return it as a string.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return string
     */
    public function getQrCode(User $user, Request $request): string;
}
