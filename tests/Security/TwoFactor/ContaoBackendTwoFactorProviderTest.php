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

use Contao\CoreBundle\Security\TwoFactor\Authenticator;
use Contao\CoreBundle\Security\TwoFactor\BackendFormRenderer;
use Contao\CoreBundle\Security\TwoFactor\BackendProvider;
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;

class ContaoBackendTwoFactorProviderTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertInstanceOf('Contao\CoreBundle\Security\TwoFactor\BackendProvider', $provider);
    }

    public function testReturnsFormRenderer(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertInstanceOf('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface', $provider->getFormRenderer());
    }

    public function testDoesNotBeginAuthenticationWithAnInvalidUser(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertFalse($provider->beginAuthentication($context));
    }

    public function testDoesNotBeginAuthenticationWithAnUserWithoutASecret(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $user = $this->createMock(User::class);
        $user->secret = null;
        $user->use2fa = true;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertFalse($provider->beginAuthentication($context));
    }

    public function testDoesNotBeginAuthenticationWith2faDisabled(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $user = $this->createMock(User::class);
        $user->secret = 'iAmASecret';
        $user->use2fa = false;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertFalse($provider->beginAuthentication($context));
    }

    public function testDoesBeginAuthenticationWith2faEnforced(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $user = $this->createMock(User::class);
        $user->secret = 'iAmASecret';
        $user->use2fa = false;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new BackendProvider($authenticator, $renderer, true);

        $this->assertTrue($provider->beginAuthentication($context));
    }

    public function testDoesBeginAuthenticationWith2faEnabled(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $user = $this->createMock(User::class);
        $user->secret = 'iAmASecret';
        $user->use2fa = true;

        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertTrue($provider->beginAuthentication($context));
    }

    public function testValidateAuthenticationCodeReturnsFalseWithAnInvalidUser(): void
    {
        $authenticator = $this->createMock(Authenticator::class);
        $renderer = $this->createMock(BackendFormRenderer::class);

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertFalse($provider->validateAuthenticationCode(null, ''));
    }

    public function testValidateAuthenticationCodeReturnsFalseWithAnInvalidCode(): void
    {
        $user = $this->createMock(User::class);
        $renderer = $this->createMock(BackendFormRenderer::class);
        $authenticator = $this->createMock(Authenticator::class);
        $authenticator
            ->expects($this->once())
            ->method('validateCode')
            ->with($user, '123456')
            ->willReturn(false)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertFalse($provider->validateAuthenticationCode($user, '123456'));
    }

    public function testValidateAuthenticationCodeSavesConfirmed2faFlag(): void
    {
        $user = $this->createMock(User::class);
        $user->confirmed2fa = false;
        $user
            ->expects($this->once())
            ->method('save')
            ->willReturn(null)
        ;

        $renderer = $this->createMock(BackendFormRenderer::class);
        $authenticator = $this->createMock(Authenticator::class);
        $authenticator
            ->expects($this->once())
            ->method('validateCode')
            ->with($user, '123456')
            ->willReturn(true)
        ;

        $provider = new BackendProvider($authenticator, $renderer, true);
        $provider->validateAuthenticationCode($user, '123456');

        $this->assertTrue($user->confirmed2fa);
    }

    public function testValidateAuthenticationCodeReturnsTrue(): void
    {
        $user = $this->createMock(User::class);
        $user->confirmed2fa = true;

        $renderer = $this->createMock(BackendFormRenderer::class);
        $authenticator = $this->createMock(Authenticator::class);
        $authenticator
            ->expects($this->once())
            ->method('validateCode')
            ->with($user, '123456')
            ->willReturn(true)
        ;

        $provider = new BackendProvider($authenticator, $renderer, false);

        $this->assertTrue($provider->validateAuthenticationCode($user, '123456'));
    }
}
