<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Security\TwoFactor;

use Contao\User;
use Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorAuthenticatorProvider;
use Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorAuthenticator;
use Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorFormRenderer;
use Contao\CoreBundle\Tests\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;

class ContaoTwoFactorAuthenticatorProviderTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertInstanceOf('Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorAuthenticatorProvider', $provider);
    }

    public function testReturnsFormRenderer(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertInstanceOf('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface', $provider->getFormRenderer());
    }

    public function testDoesNotBeginAuthenticationWithAnInvalidUser(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);
        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertFalse($provider->beginAuthentication($context));
    }

    public function testDoesNotBeginAuthenticationWithAnUserWithoutASecret(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);
        $user = $this->createMock(User::class);
        $user
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn(null)
        ;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertFalse($provider->beginAuthentication($context));
    }

    public function testDoesBeginAuthentication(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);
        $user = $this->createMock(User::class);
        $user
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn('iAmASecret')
        ;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertTrue($provider->beginAuthentication($context));
    }

    public function testValidateAuthenticationCodeReturnsFalseWithAnInvalidUser(): void
    {
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertFalse($provider->validateAuthenticationCode(null, ''));
    }

    public function testValidateAuthenticationCodeReturnsFalseWithAnInvalidCode(): void
    {
        $user = $this->createMock(User::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $authenticator
            ->expects($this->once())
            ->method('validateCode')
            ->with($user, '123456')
            ->willReturn(false)
        ;

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertFalse($provider->validateAuthenticationCode($user, '123456'));
    }

    public function testValidateAuthenticationCodeReturnsTrue(): void
    {
        $user = $this->createMock(User::class);
        $renderer = $this->createMock(ContaoTwoFactorFormRenderer::class);
        $authenticator = $this->createMock(ContaoTwoFactorAuthenticator::class);
        $authenticator
            ->expects($this->once())
            ->method('validateCode')
            ->with($user, '123456')
            ->willReturn(true)
        ;

        $provider = new ContaoTwoFactorAuthenticatorProvider($authenticator, $renderer);

        $this->assertTrue($provider->validateAuthenticationCode($user, '123456'));
    }
}
