<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\Authentication\Provider;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\Authentication\Provider\ContaoAuthenticationProvider;
use Contao\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Tests the ContaoAuthenticationProvider class.
 */
class ContaoAuthenticationProviderTest extends TestCase
{
    protected $logger;
    protected $session;
    protected $translator;

    protected $userProvider;
    protected $userChecker;
    protected $providerKey;
    protected $encoderFactory;
    protected $hideUserNotFoundExceptions;

    protected $user;
    protected $token;
    protected $encoder;
    protected $flashBag;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        unset($GLOBALS['TL_HOOKS']);

        $this->userProvider = $this->createMock(UserProviderInterface::class);
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->providerKey = 'contao_frontend';
        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $this->hideUserNotFoundExceptions = false;

        $this->mockSession();
        $this->mockLogger();
        $this->mockTranslator();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $authenticationProvider = $this->mockProvider();

        $this->assertInstanceOf(
            'Contao\CoreBundle\Security\Authentication\Provider\ContaoAuthenticationProvider',
            $authenticationProvider
        );
    }

    /**
     * Tests if a BadCredentialsException is thrown with an invalid user.
     */
    public function testThrowsBadCredentialsExceptionWithAnInvalidUser(): void
    {
        $this->mockUser();
        $this->mockToken();

        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $encoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->willReturn(false)
        ;

        $authenticationProvider = $this->mockProvider(null, null, $encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests if a BadCredentialsException is thrown with an invalid FrontendUser.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the checkCredentials hook has been deprecated %s.
     */
    public function testThrowsBadCredentialsExceptionWithAFrontendUser(): void
    {
        $this->providerKey = 'contao_frontend';
        $this->mockUser('Contao\FrontendUser', 'foobar');
        $this->mockToken();
        $this->mockTranslator(true);
        $this->mockEncoder(false);
        $this->mockFlashBag('contao.FE.error');
        $this->mockSession(true);
        $this->mockLogger('Invalid password submitted for username foobar');

        $authenticationProvider = $this->mockProvider(null, null, $this->encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests if a BadCredentialsException is thrown with an invalid BackendUser.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the checkCredentials hook has been deprecated %s.
     */
    public function testThrowsBadCredentialsExceptionWithABackendUser(): void
    {
        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser', 'foobar');
        $this->mockToken();
        $this->mockTranslator(true);
        $this->mockEncoder(false);
        $this->mockFlashBag('contao.BE.error');
        $this->mockSession(true);
        $this->mockLogger('Invalid password submitted for username foobar');

        $authenticationProvider = $this->mockProvider(null, null, $this->encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests a successful authentication of a BackendUser.
     */
    public function testSuccessfulBackendUserAuthentication(): void
    {
        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser');
        $this->mockEncoder(true);
        $this->mockToken(true);

        $authenticationProvider = $this->mockProvider(null, null, $this->encoder);
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests a successful authentication of a FrontendUser.
     */
    public function testSuccessfulFrontendUserAuthentication(): void
    {
        $this->providerKey = 'contao_frontend';
        $this->mockUser('Contao\FrontendUser');
        $this->mockEncoder(true);
        $this->mockToken(true);

        $authenticationProvider = $this->mockProvider(null, null, $this->encoder);
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests the execution of the checkCredentials hook.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the checkCredentials hook has been deprecated %s.
     */
    public function testExecutesTheCheckCredentialsHook(): void
    {
        $GLOBALS['TL_HOOKS'] = [
            'checkCredentials' => [[\get_class($this), 'executeCheckCredentialsHookCallback']],
        ];

        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser');
        $this->mockEncoder(false);
        $this->mockToken(false, 'username', 'password');

        $authenticationProvider = $this->mockProvider(null, null, $this->encoder);

        $this->assertEmpty($authenticationProvider->checkAuthentication($this->user, $this->token));
    }

    /**
     * checkCredentials hook stub.
     *
     * @param string $username
     * @param string $credentials
     * @param User   $user
     *
     * @return bool
     */
    public static function executeCheckCredentialsHookCallback(string $username, string $credentials, User $user): bool
    {
        self::assertSame('username', $username);
        self::assertSame('password', $credentials);
        self::assertInstanceOf('Contao\User', $user);

        return true;
    }

    /**
     * Mocks the User with an optional username.
     *
     * @param string      $class
     * @param string|null $expectedUsername
     */
    private function mockUser(string $class = null, string $expectedUsername = null): void
    {
        if (null === $class) {
            $this->user = $this->createMock(UserInterface::class);
        } else {
            $this->user = $this->createPartialMock($class, ['getUsername', 'save']);
        }

        if (null !== $expectedUsername) {
            $this->user->username = $expectedUsername;

            $this->user
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($expectedUsername)
            ;
        }
    }

    /**
     * Mocks a Token.
     *
     * @param bool        $supported
     * @param string|null $username
     * @param string|null $credentials
     */
    private function mockToken(bool $supported = false, string $username = null, string $credentials = null): void
    {
        $this->token = $this->createPartialMock(
            UsernamePasswordToken::class,
            ['getCredentials', 'getUser', 'getProviderKey', 'getUsername']
        );

        if (true === $supported) {
            $this->token
                ->expects($this->any())
                ->method('getProviderKey')
                ->willReturn('key')
            ;

            $this->token
                ->expects($this->once())
                ->method('getCredentials')
                ->willReturn('foo')
            ;
        }

        if (null !== $username) {
            $this->token
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($username)
            ;
        }

        if (null !== $credentials) {
            $this->token
                ->expects($this->any())
                ->method('getCredentials')
                ->willReturn($credentials)
            ;
        }
    }

    /**
     * Mocks a ContaoAuthenticationProvider.
     *
     * @param User|null                     $user
     * @param UserCheckerInterface|null     $userChecker
     * @param PasswordEncoderInterface|null $passwordEncoder
     *
     * @return ContaoAuthenticationProvider
     */
    private function mockProvider(User $user = null, UserCheckerInterface $userChecker = null, PasswordEncoderInterface $passwordEncoder = null): ContaoAuthenticationProvider
    {
        $userProvider = $this->createMock(UserProviderInterface::class);

        if (null !== $user) {
            $userProvider
                ->expects($this->once())
                ->method('loadUserByUsername')
                ->willReturn($user)
            ;
        }

        if (null === $userChecker) {
            $userChecker = $this->createMock(UserCheckerInterface::class);
        }

        if (null === $passwordEncoder) {
            $passwordEncoder = new PlaintextPasswordEncoder();
        }

        $encoderFactory = $this->createMock(EncoderFactoryInterface::class);

        $encoderFactory
            ->expects($this->any())
            ->method('getEncoder')
            ->willReturn($passwordEncoder)
        ;

        return new ContaoAuthenticationProvider(
            $userProvider,
            $userChecker,
            $this->providerKey,
            $encoderFactory,
            $this->hideUserNotFoundExceptions,
            $this->logger,
            $this->session,
            $this->translator
        );
    }

    /**
     * Mocks the logger service with an optional message.
     *
     * @param string|null $message
     */
    private function mockLogger(string $message = null): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        if (null !== $message) {
            $context = [
                'contao' => new ContaoContext(
                    'Contao\CoreBundle\Security\Authentication\Provider\ContaoAuthenticationProvider::checkAuthentication',
                    ContaoContext::ACCESS
                ),
            ];

            $this->logger
                ->expects($this->once())
                ->method('info')
                ->with($message, $context)
            ;
        }
    }

    /**
     * Mocks a translator with an optional translation.
     *
     * @param bool $withTranslation
     */
    private function mockTranslator(bool $withTranslation = false): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        if (true === $withTranslation) {
            $this->translator
                ->expects($this->once())
                ->method('trans')
                ->with('ERR.invalidLogin', [], 'contao_default')
                ->willReturn('Login failed (note that usernames and passwords are case-sensitive)!')
            ;
        }
    }

    /**
     * Mocks an encoder.
     *
     * @param bool $isPasswordValid
     */
    private function mockEncoder(bool $isPasswordValid = false): void
    {
        $this->encoder = $this->createMock(PasswordEncoderInterface::class);
        $this->encoder
            ->expects($this->once())
            ->method('isPasswordValid')
            ->willReturn($isPasswordValid)
        ;
    }

    /**
     * Mocks a flashBag.
     *
     * @param string $type
     */
    private function mockFlashBag(string $type): void
    {
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->flashBag
            ->expects($this->once())
            ->method('set')
            ->with($type, 'Login failed (note that usernames and passwords are case-sensitive)!')
        ;
    }

    /**
     * Mocks a session with an optional flashBag.
     *
     * @param bool $withFlashBag
     */
    private function mockSession(bool $withFlashBag = false): void
    {
        $this->session = $this->createMock(Session::class);

        if (true === $withFlashBag) {
            $this->session
                ->expects($this->once())
                ->method('getFlashBag')
                ->willReturn($this->flashBag)
            ;
        }
    }
}
