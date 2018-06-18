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

use Contao\BackendUser;
use Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorAuthenticator;
use Contao\CoreBundle\Tests\TestCase;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Symfony\Component\HttpFoundation\Request;

class ContaoTwoFactorAuthenticatorTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $authenticator = new ContaoTwoFactorAuthenticator();

        $this->assertInstanceOf('Contao\CoreBundle\Security\TwoFactor\ContaoTwoFactorAuthenticator', $authenticator);
    }

    public function testCodeValidation(): void
    {
        $secret = random_bytes(128);
        $totp = TOTP::create(Base32::encodeUpperUnpadded($secret));

        $user = $this->createMock(BackendUser::class);
        $user
            ->expects($this->exactly(2))
            ->method('getSecret')
            ->willReturn($secret)
        ;

        $authenticator = new ContaoTwoFactorAuthenticator();

        $this->assertTrue($authenticator->validateCode($user, $totp->now()));
        $this->assertFalse($authenticator->validateCode($user, 'foobar'));
    }

    public function testProvisionUriGeneration(): void
    {
        $secret = random_bytes(128);

        $user = $this->createMock(BackendUser::class);
        $user
            ->expects($this->exactly(2))
            ->method('getUsername')
            ->willReturn('foobar')
        ;

        $user
            ->expects($this->exactly(2))
            ->method('getSecret')
            ->willReturn($secret)
        ;

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->exactly(2))
            ->method('getSchemeAndHttpHost')
            ->willReturn('https://example.com')
        ;

        $authenticator = new ContaoTwoFactorAuthenticator();

        $this->assertSame(
            sprintf('otpauth://totp/https%%3A%%2F%%2Fexample.com:foobar@https%%3A%%2F%%2Fexample.com?secret=%s&issuer=https%%3A%%2F%%2Fexample.com', Base32::encodeUpperUnpadded($secret)),
            $authenticator->getProvisionUri($user, $request)
        );

        $this->assertNotSame(
            sprintf('otpauth://totp/https%%3A%%2F%%2Fexample.com:foobar@https%%3A%%2F%%2Fexample.com?secret=%s&issuer=https%%3A%%2F%%2Fexample.com', Base32::encodeUpperUnpadded('foobar')),
            $authenticator->getProvisionUri($user, $request)
        );
    }

    public function testQrCodeGeneration(): void
    {
        $secret = 'foobar';
        $beginSvg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="200" height="200" viewBox="0 0 200 200"><rect x="0" y="0" width="200" height="200" fill="#fefefe"/>
SVG;

        $user = $this->createMock(BackendUser::class);
        $user
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('foobar')
        ;

        $user
            ->expects($this->once())
            ->method('getSecret')
            ->willReturn($secret)
        ;

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('getSchemeAndHttpHost')
            ->willReturn('https://example.com')
        ;

        $authenticator = new ContaoTwoFactorAuthenticator();
        $svg = $authenticator->getQrCode($user, $request);

        $this->assertSame(7193, \strlen($svg));
        $this->assertSame(0, strpos($svg, $beginSvg));
    }
}
