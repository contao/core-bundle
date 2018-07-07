<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Security\Authentication;

use Contao\BackendUser;
use Contao\CoreBundle\Security\Authentication\FrontendPreviewAuthenticator;
use Contao\CoreBundle\Security\Authentication\Token\FrontendPreviewToken;
use Contao\CoreBundle\Tests\TestCase;
use Contao\FrontendUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FrontendPreviewAuthenticatorTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $authenticator = $this->mockAuthenticator();

        $this->assertInstanceOf(
            'Contao\CoreBundle\Security\Authentication\FrontendPreviewAuthenticator',
            $authenticator
        );
    }

    public function testDoesNotAuthenticateIfTheSessionIsNotStarted(): void
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $authenticator = $this->mockAuthenticator($session);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    public function testDoesNotAuthenticateIfTheTokenStorageIsEmpty(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $authenticator = $this->mockAuthenticator(null, $tokenStorage);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    public function testDoesNotAuthenticateIfTheTokenIsNotAuthenticated(): void
    {
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

        $authenticator = $this->mockAuthenticator(null, $tokenStorage);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    public function testDoesNotAuthenticateIfTheTokenDoesNotContainABackendUser(): void
    {
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

        $authenticator = $this->mockAuthenticator(null, $tokenStorage);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    /**
     * @param bool  $isAdmin
     * @param mixed $amg
     * @param bool  $isValid
     *
     * @dataProvider getAccessPermissions
     */
    public function testChecksTheBackendUsersAccessPermissions(bool $isAdmin, $amg, bool $isValid): void
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

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider
            ->expects($this->exactly((int) $isValid))
            ->method('loadUserByUsername')
            ->willReturn($this->createMock(UserInterface::class))
        ;

        $authenticator = $this->mockAuthenticator(null, $tokenStorage, $userProvider);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    /**
     * @return array
     */
    public function getAccessPermissions(): array
    {
        return [
            [true, null, true],
            [false, null, false],
            [false, 'foobar', false],
            [false, [], false],
            [false, ['foobar'], true],
        ];
    }

    public function testDoesNotAuthenticateIfThereIsNotFrontendUser(): void
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

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->willThrowException(new UsernameNotFoundException())
        ;

        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('info')
            ->with('Could not find a front end user with the username "foobar"')
        ;

        $authenticator = $this->mockAuthenticator(null, $tokenStorage, $userProvider, $logger);

        $this->assertFalse($authenticator->authenticateFrontendUser('foobar', false));
    }

    /**
     * @param bool  $isAdmin
     * @param mixed $amg
     * @param mixed $groups
     * @param bool  $isValid
     *
     * @dataProvider getFrontendGroupAccessPermissions
     */
    public function testChecksTheBackendUsersFrontendGroupAccess(bool $isAdmin, $amg, $groups, bool $isValid): void
    {
        $backendUser = $this->createMock(BackendUser::class);
        $backendUser->isAdmin = $isAdmin;
        $backendUser->amg = $amg;

        $frontendUser = $this->createMock(FrontendUser::class);
        $frontendUser->groups = $groups;

        $frontendUser
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

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider
            ->method('loadUserByUsername')
            ->willReturn($frontendUser)
        ;

        $authenticator = $this->mockAuthenticator(null, $tokenStorage, $userProvider);

        $this->assertSame($isValid, $authenticator->authenticateFrontendUser('foobar', false));
    }

    /**
     * @return array
     */
    public function getFrontendGroupAccessPermissions(): array
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

    public function testAuthenticatesAFrontendUserWithUnpublishedElementsHidden(): void
    {
        $backendUser = $this->createMock(BackendUser::class);
        $backendUser->isAdmin = true;

        $frontendUser = $this->createMock(FrontendUser::class);

        $frontendUser
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

        $session = $this->mockSession();
        $session->start();

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider
            ->method('loadUserByUsername')
            ->willReturn($frontendUser)
        ;

        $authenticator = $this->mockAuthenticator($session, $tokenStorage, $userProvider);

        $this->assertTrue($authenticator->authenticateFrontendUser('foobar', false));
        $this->assertTrue($session->has(FrontendUser::SECURITY_SESSION_KEY));

        $token = unserialize($session->get(FrontendUser::SECURITY_SESSION_KEY), ['allowed_classes' => true]);

        $this->assertInstanceOf(FrontendPreviewToken::class, $token);
        $this->assertInstanceOf(FrontendUser::class, $token->getUser());
        $this->assertFalse($token->showUnpublished());
    }

    public function testAuthenticatesAFrontendUserWithUnpublishedElementsVisible(): void
    {
        $backendUser = $this->createMock(BackendUser::class);
        $backendUser->isAdmin = true;

        $frontendUser = $this->createMock(FrontendUser::class);

        $frontendUser
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

        $session = $this->mockSession();
        $session->start();

        $userProvider = $this->createMock(UserProviderInterface::class);

        $userProvider
            ->method('loadUserByUsername')
            ->willReturn($frontendUser)
        ;

        $authenticator = $this->mockAuthenticator($session, $tokenStorage, $userProvider);

        $this->assertTrue($authenticator->authenticateFrontendUser('foobar', true));
        $this->assertTrue($session->has(FrontendUser::SECURITY_SESSION_KEY));

        $token = unserialize($session->get(FrontendUser::SECURITY_SESSION_KEY), ['allowed_classes' => true]);

        $this->assertInstanceOf(FrontendPreviewToken::class, $token);
        $this->assertInstanceOf(FrontendUser::class, $token->getUser());
        $this->assertTrue($token->showUnpublished());
    }

    public function testDoesNotAuthenticateGuestsIfThereIsNoBackendUser(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $session = $this->mockSession();
        $session->start();

        $authenticator = $this->mockAuthenticator($session, $tokenStorage);

        $this->assertFalse($authenticator->authenticateFrontendGuest(false));
    }

    public function testAuthenticatesGuestsWithUnpublishedElementsHidden(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(BackendUser::class))
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $session = $this->mockSession();
        $session->start();

        $authenticator = $this->mockAuthenticator($session, $tokenStorage);

        $this->assertTrue($authenticator->authenticateFrontendGuest(false));
        $this->assertTrue($session->has(FrontendUser::SECURITY_SESSION_KEY));

        $token = unserialize($session->get(FrontendUser::SECURITY_SESSION_KEY), ['allowed_classes' => true]);

        $this->assertInstanceOf(FrontendPreviewToken::class, $token);
        $this->assertSame('anon.', $token->getUser());
        $this->assertFalse($token->showUnpublished());
    }

    public function testAuthenticatesGuestsWithUnpublishedElementsVisible(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true)
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(BackendUser::class))
        ;

        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $session = $this->mockSession();
        $session->start();

        $authenticator = $this->mockAuthenticator($session, $tokenStorage);

        $this->assertTrue($authenticator->authenticateFrontendGuest(true));
        $this->assertTrue($session->has(FrontendUser::SECURITY_SESSION_KEY));

        $token = unserialize($session->get(FrontendUser::SECURITY_SESSION_KEY), ['allowed_classes' => true]);

        $this->assertInstanceOf(FrontendPreviewToken::class, $token);
        $this->assertSame('anon.', $token->getUser());
        $this->assertTrue($token->showUnpublished());
    }

    public function testRemovesTheAuthenticationFromTheSession(): void
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

        $authenticator = $this->mockAuthenticator($session);

        $this->assertTrue($authenticator->removeFrontendAuthentication());
    }

    public function testDoesNotRemoveTheAuthenticationIfTheSessionIsNotStarted(): void
    {
        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false)
        ;

        $authenticator = $this->mockAuthenticator($session);

        $this->assertFalse($authenticator->removeFrontendAuthentication());
    }

    public function testDoesNotRemoveTheAuthenticationIfTheSessionDoesNotContainAToken(): void
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

        $authenticator = $this->mockAuthenticator($session);

        $this->assertFalse($authenticator->removeFrontendAuthentication());
    }

    /**
     * Mocks an authenticator.
     *
     * @param SessionInterface|null      $session
     * @param TokenStorageInterface|null $tokenStorage
     * @param UserProviderInterface|null $userProvider
     * @param LoggerInterface|null       $logger
     *
     * @return FrontendPreviewAuthenticator
     */
    private function mockAuthenticator(SessionInterface $session = null, TokenStorageInterface $tokenStorage = null, UserProviderInterface $userProvider = null, LoggerInterface $logger = null): FrontendPreviewAuthenticator
    {
        if (null === $session) {
            $session = $this->createMock(SessionInterface::class);

            $session
                ->method('isStarted')
                ->willReturn(true)
            ;
        }

        if (null === $tokenStorage) {
            $tokenStorage = $this->createMock(TokenStorageInterface::class);
        }

        if (null === $userProvider) {
            $userProvider = $this->createMock(UserProviderInterface::class);
        }

        if (null === $logger) {
            $logger = $this->createMock(LoggerInterface::class);
        }

        return new FrontendPreviewAuthenticator($session, $tokenStorage, $userProvider, $logger);
    }
}
