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

use Contao\CoreBundle\Event\CheckCredentialsEvent;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\Authentication\Provider\ContaoAuthenticationProvider;
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @var string
     */
    protected $providerKey;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var bool
     */
    protected $hideUserNotFoundExceptions;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var PasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var FlashBagInterface
     */
    protected $flashBag;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        unset($GLOBALS['TL_HOOKS']);

        $this->framework = $this->mockContaoFramework();
        $this->userProvider = $this->createMock(UserProviderInterface::class);
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->providerKey = 'contao_frontend';
        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $this->hideUserNotFoundExceptions = false;

        $this->createSessionMock();
        $this->mockLogger();
        $this->mockTranslator();
        $this->mockEventDispatcher(false);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $authenticationProvider = $this->getProvider();

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

        $authenticationProvider = $this->getProvider(null, null, $encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests if a BadCredentialsException is thrown with a FrontendUser and an invalid password.
     */
    public function testThrowsBadCredentialsExceptionWithAFrontendUser(): void
    {
        $this->providerKey = 'contao_frontend';
        $this->mockUser('Contao\FrontendUser', 'foobar');
        $this->mockToken(false, 'foobar', '');
        $this->mockTranslator(true);
        $this->mockEncoder();
        $this->mockFlashBag('contao.FE.error');
        $this->createSessionMock(true);
        $this->mockLogger('Invalid password submitted for username foobar');
        $this->mockEventDispatcher(true, 'foobar', '', $this->user);

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests if a BadCredentialsException is thrown with a BackendUser and an invalid password.
     */
    public function testThrowsBadCredentialsExceptionWithABackendUser(): void
    {
        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser', 'foobar');
        $this->mockToken(false, 'foobar', '');
        $this->mockTranslator(true);
        $this->mockEncoder();
        $this->mockFlashBag('contao.BE.error');
        $this->createSessionMock(true);
        $this->mockLogger('Invalid password submitted for username foobar');
        $this->mockEventDispatcher(true, 'foobar', '', $this->user);

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

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

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests a successful authentication of a user when the vote of the CheckCredentialsEvent is positive.
     */
    public function testSuccessfulBackendUserAuthenticationWhenEventVoteIsPositive(): void
    {
        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser');
        $this->mockToken(false, 'foobar', '');
        $this->mockTranslator();
        $this->mockEncoder();
        $this->mockFlashBag();
        $this->createSessionMock();
        $this->mockLogger();
        $this->mockEventDispatcher(true, 'foobar', '', $this->user, true);

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

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

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests the execution of the checkCredentials hook.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the checkCredentials hook has been deprecated %s.
     */
    public function testExecutesTheCheckCredentialsHookReturnsTrue(): void
    {
        $this->framework
            ->expects($this->once())
            ->method('createInstance')
            ->willReturn($this)
        ;

        $GLOBALS['TL_HOOKS'] = [
            'checkCredentials' => [[\get_class($this), 'executeCheckCredentialsHookCallbackReturnsTrue']],
        ];

        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser');
        $this->mockEncoder(false);
        $this->mockToken(false, 'username', 'password');
        $this->mockEventDispatcher(true, 'username', 'password', $this->user, false);

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

        $authenticationProvider->checkAuthentication($this->user, $this->token);
    }

    /**
     * Tests the execution of the checkCredentials hook.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the checkCredentials hook has been deprecated %s.
     */
    public function testExecutesTheCheckCredentialsHookReturnsFalse(): void
    {
        $this->framework
            ->expects($this->once())
            ->method('createInstance')
            ->willReturn($this)
        ;

        $GLOBALS['TL_HOOKS'] = [
            'checkCredentials' => [[\get_class($this), 'executeCheckCredentialsHookCallbackReturnsFalse']],
        ];

        $this->providerKey = 'contao_backend';
        $this->mockUser('Contao\BackendUser', 'username');
        $this->mockToken(false, 'username', 'password');
        $this->mockEncoder(false);
        $this->mockEventDispatcher(true, 'username', 'password', $this->user, false);
        $this->mockFlashBag('contao.BE.error');
        $this->mockTranslator(true);
        $this->createSessionMock(true);
        $this->mockLogger('Invalid password submitted for username username');

        $authenticationProvider = $this->getProvider(null, null, $this->encoder);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');
        $authenticationProvider->checkAuthentication($this->user, $this->token);
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
    public static function executeCheckCredentialsHookCallbackReturnsTrue(string $username, string $credentials, User $user): bool
    {
        self::assertSame('username', $username);
        self::assertSame('password', $credentials);
        self::assertInstanceOf('Contao\User', $user);

        return true;
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
    public static function executeCheckCredentialsHookCallbackReturnsFalse(string $username, string $credentials, User $user): bool
    {
        self::assertSame('username', $username);
        self::assertSame('password', $credentials);
        self::assertInstanceOf('Contao\User', $user);

        return false;
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
                ->expects($this->atLeastOnce())
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
     * Returns a ContaoAuthenticationProvider.
     *
     * @param User|null                     $user
     * @param UserCheckerInterface|null     $userChecker
     * @param PasswordEncoderInterface|null $passwordEncoder
     *
     * @return ContaoAuthenticationProvider
     */
    private function getProvider(User $user = null, UserCheckerInterface $userChecker = null, PasswordEncoderInterface $passwordEncoder = null): ContaoAuthenticationProvider
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
            $this->translator,
            $this->eventDispatcher,
            $this->framework
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
     * @param bool|null $isPasswordValid
     */
    private function mockEncoder(bool $isPasswordValid = null): void
    {
        $this->encoder = $this->createMock(PasswordEncoderInterface::class);

        if (null !== $isPasswordValid) {
            $this->encoder
                ->expects($this->once())
                ->method('isPasswordValid')
                ->willReturn($isPasswordValid)
            ;
        }
    }

    /**
     * Mocks a flashBag.
     *
     * @param string|null $type
     */
    private function mockFlashBag(string $type = null): void
    {
        $this->flashBag = $this->createMock(FlashBagInterface::class);

        if (null !== $type) {
            $this->flashBag
                ->expects($this->once())
                ->method('set')
                ->with($type, 'Login failed (note that usernames and passwords are case-sensitive)!')
            ;
        }
    }

    /**
     * Mocks a session mock with an optional flashBag.
     *
     * @param bool $withFlashBag
     */
    private function createSessionMock(bool $withFlashBag = false): void
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

    /**
     * Mocks the event dispatcher.
     *
     * @param bool      $expectsDispatchEvent
     * @param string    $username
     * @param string    $credentials
     * @param User|null $user
     * @param bool      $vote
     */
    private function mockEventDispatcher(bool $expectsDispatchEvent, string $username = '', string $credentials = '', User $user = null, bool $vote = false): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        if (true === $expectsDispatchEvent) {
            $checkCredentialsEvent = new CheckCredentialsEvent($username, $credentials, $user);
            $checkCredentialsEvent->vote($vote);

            $this->eventDispatcher
                ->expects($this->once())
                ->method('dispatch')
                ->with(CheckCredentialsEvent::NAME)
                ->willReturn($checkCredentialsEvent)
            ;
        }

        if (false === $expectsDispatchEvent) {
            $this->eventDispatcher
                ->expects($this->never())
                ->method('dispatch')
            ;
        }
    }
}
