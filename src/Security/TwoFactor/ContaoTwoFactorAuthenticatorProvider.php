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

class ContaoTwoFactorAuthenticatorProvider implements TwoFactorProviderInterface
{
    /**
     * @var ContaoTwoFactorAuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var ContaoTwoFactorFormRenderer
     */
    private $renderer;

    /**
     * @param ContaoTwoFactorAuthenticatorInterface $authenticator
     * @param ContaoTwoFactorFormRenderer           $renderer
     */
    public function __construct(ContaoTwoFactorAuthenticatorInterface $authenticator, ContaoTwoFactorFormRenderer $renderer)
    {
        $this->authenticator = $authenticator;
        $this->renderer = $renderer;
    }

    /**
     * @param AuthenticationContextInterface $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (!$user->getSecret()) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed  $user
     * @param string $authenticationCode
     *
     * @return bool
     */
    public function validateAuthenticationCode($user, string $authenticationCode): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$this->authenticator->validateCode($user, $authenticationCode)) {
            return false;
        }

        return true;
    }

    /**
     * @return TwoFactorFormRendererInterface
     */
    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->renderer;
    }
}
