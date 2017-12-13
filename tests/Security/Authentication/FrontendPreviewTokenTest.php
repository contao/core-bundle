<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Security\Authentication;

use Contao\CoreBundle\Security\Authentication\FrontendPreviewToken;
use Contao\FrontendUser;
use PHPUnit\Framework\TestCase;

class FrontendPreviewTokenTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $user = $this->createMock(FrontendUser::class);

        $user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn([])
        ;

        $token = new FrontendPreviewToken($user);

        $this->assertInstanceOf('Contao\CoreBundle\Security\Authentication\FrontendPreviewToken', $token);
    }

    public function testIsAuthenticated()
    {
        $user = $this->createMock(FrontendUser::class);

        $user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(['foobar'])
        ;

        $token = new FrontendPreviewToken($user);

        $this->assertTrue($token->isAuthenticated());
    }

    public function testHasNoCredentials()
    {
        $user = $this->createMock(FrontendUser::class);

        $user
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn([])
        ;

        $token = new FrontendPreviewToken($user);

        $this->assertNull($token->getCredentials());
    }
}
