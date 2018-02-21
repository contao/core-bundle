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
     * Generate the URL of a QR code, which can be scanned by an app.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return string
     */
    public function getUrl(User $user, Request $request): string;

    /**
     * Generate the content for a QR-Code to be scanned.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return string
     */
    public function getQRContent(User $user, Request $request): string;
}
