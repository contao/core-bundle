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

class Provider implements TwoFactorProviderInterface
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var TwoFactorFormRendererInterface
     */
    private $formRenderer;

    /**
     * @var bool
     */
    private $enforceTwoFactor;

    /**
     * @param Authenticator       $authenticator
     * @param TwoFactorFormRendererInterface $renderer
     * @param bool                $enforceTwoFactor
     */
    public function __construct(Authenticator $authenticator, TwoFactorFormRendererInterface $formRenderer, bool $enforceTwoFactor)
    {
        $this->authenticator = $authenticator;
        $this->formRenderer = $formRenderer;
        $this->enforceTwoFactor = $enforceTwoFactor;
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

        if (!$this->enforceTwoFactor && !(bool) $user->useTwoFactor) {
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

        // 2FA is now confirmed, save the user flag
        if ($this->enforceTwoFactor && !(bool) $user->confirmedTwoFactor) {
            $user->confirmedTwoFactor = true;
            $user->save();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}
