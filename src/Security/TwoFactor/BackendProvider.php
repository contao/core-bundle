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
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class BackendProvider implements TwoFactorProviderInterface
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var BackendFormRenderer
     */
    private $renderer;

    /** @var bool */
    private $enforce2fa;

    /**
     * @param Authenticator       $authenticator
     * @param BackendFormRenderer $renderer
     * @param bool                               $enforce2fa
     */
    public function __construct(Authenticator $authenticator, BackendFormRenderer $renderer, bool $enforce2fa)
    {
        $this->authenticator = $authenticator;
        $this->renderer = $renderer;
        $this->enforce2fa = $enforce2fa;
    }

    /**
     * {@inheritdoc}
     */
    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (!(bool) $user->secret) {
            return false;
        }

        if (!$this->enforce2fa && !(bool) $user->use2fa) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthenticationCode($user, string $authenticationCode): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$this->authenticator->validateCode($user, $authenticationCode)) {
            return false;
        }

        // 2FA is now confirmed, save flag on user
        if ($this->enforce2fa && !(bool) $user->confirmed2fa) {
            $user->confirmed2fa = true;
            $user->save();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->renderer;
    }
}
