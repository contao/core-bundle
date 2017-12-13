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

use Contao\BackendUser;
use Contao\CoreBundle\Security\Authentication\FrontendPreviewAuthenticator;
use Contao\CoreBundle\Security\User\FrontendUserProvider;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class FrontendPreviewAuthenticatorTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $authenticator = new FrontendPreviewAuthenticator(
            $this->mockSession(),
            $this->mockTokenStorage(FrontendUser::class),
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertInstanceOf('Contao\CoreBundle\Security\Authentication\FrontendPreviewAuthenticator', $authenticator);
    }

    public function testCannotAuthenticateIfTheSessionIsNotStarted(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    public function testCannotAuthenticateIfTokenStorageIsEmpty(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    public function testCannotAuthenticateIfTheTokenIsNotAuthenticated(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    public function testCannotAuthenticateIfTheTokenDoesNotContainABackendUser(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class))
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    /**
     * @param bool  $isAdmin
     * @param mixed $amg
     * @param bool  $isValid
     *
     * @dataProvider getChecksBackendUserAccessPermissionsData
     */
    public function testChecksBackendUserAccessPermissions(bool $isAdmin, $amg, bool $isValid): void
    {
        $user = $this->createMock(BackendUser::class);
        $user->isAdmin = $isAdmin;
        $user->amg = $amg;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $userProvider = $this->createMock(FrontendUserProvider::class);
        $userProvider
            ->expects($this->exactly((int) $isValid))
            ->method('loadUserByUsername')
            ->willReturn($this->createMock(UserInterface::class))
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $userProvider,
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    public function getChecksBackendUserAccessPermissionsData()
    {
        return [
            [true, null, true],
            [false, null, false],
            [false, 'foobar', false],
            [false, [], false],
            [false, ['foobar'], true],
        ];
    }

    public function testFailsAuthenticationIfUserIsNotFound(): void
    {
        $user = $this->createMock(BackendUser::class);
        $user->isAdmin = true;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $userProvider = $this->createMock(FrontendUserProvider::class);
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->willThrowException(new UsernameNotFoundException())
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $userProvider,
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar'));
    }

    /**
     * @param mixed $isAdmin
     * @param mixed $amg
     * @param mixed $groups
     * @param bool  $isValid
     *
     * @dataProvider getBackendUserAccessToFrontendGroupsData
     */
    public function testBackendUserAccessToFrontendGroups($isAdmin, $amg, $groups, bool $isValid): void
    {
        $backendUser = $this->createMock(BackendUser::class);
        $backendUser->isAdmin = $isAdmin;
        $backendUser->amg = $amg;

        $frontendUser = $this->createMock(FrontendUser::class);
        $frontendUser->groups = $groups;
        $frontendUser
            ->expects($this->any())
            ->method('getRoles')
            ->willReturn([])
        ;

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($backendUser)
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $userProvider = $this->createMock(FrontendUserProvider::class);
        $userProvider
            ->expects($this->any())
            ->method('loadUserByUsername')
            ->willReturn($frontendUser)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $tokenStorage,
            $userProvider,
            $this->createMock(LoggerInterface::class)
        );

        $this->assertSame($isValid, $authenticator->authenticateFrontendUser('foobar'));
    }

    public function getBackendUserAccessToFrontendGroupsData()
    {
        return [
            [false, null, null, false],
            [true, null, null, true],
            [false, [], [], false],
            [false, ['foo', 'bar'], [], false],
            [false, [], ['foo', 'bar'], false],
            [false, ['foo', 'bar'], ['foo', 'bar'], true],
            [false, ['foo', 'bar'], ['foo'], true],
        ];
    }

    public function testCannotRemoveIfSessionIsNotStarted(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $this->mockTokenStorage(FrontendUser::class),
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->removeFrontendUser());
    }

    public function testCannotRemoveIfSessionDoesNotContainAToken(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(false)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $this->mockTokenStorage(FrontendUser::class),
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($authenticator->removeFrontendUser());
    }

    public function testRemovesTokenFromSession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('has')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
            ->willReturn(true)
        ;

        $session
            ->expects($this->once())
            ->method('remove')
            ->with(FrontendUser::SECURITY_SESSION_KEY)
        ;

        $authenticator = new FrontendPreviewAuthenticator(
            $session,
            $this->mockTokenStorage(FrontendUser::class),
            $this->createMock(FrontendUserProvider::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertTrue($authenticator->removeFrontendUser());
    }
}
