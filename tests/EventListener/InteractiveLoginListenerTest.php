<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\User;

use Contao\CoreBundle\EventListener\InteractiveLoginListener;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Tests the InteractiveLoginListener class.
 */
class InteractiveLoginListenerTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var InteractiveLoginEvent
     */
    protected $interactiveLoginEvent;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        unset($GLOBALS['TL_HOOKS']);
        $this->framework = $this->mockContaoFramework();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->mockLogger();

        $listener = new InteractiveLoginListener($this->logger, $this->framework);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\InteractiveLoginListener', $listener);
    }

    /**
     * Tests if the listener immediatly returns if no user is available.
     */
    public function testImmediateReturnWhenNoUserIsGiven(): void
    {
        $this->mockLogger();
        $this->mockInteractiveLoginEvent();

        $listener = new InteractiveLoginListener($this->logger, $this->framework);
        $listener->onInteractiveLogin($this->interactiveLoginEvent);
    }

    /**
     * Tests the logger message with a valid user given.
     *
     * @group legacy
     *
     * @expectedDeprecation Using InteractiveLoginListener::triggerLegacyPostLoginHook has been deprecated %s.
     */
    public function testLoggerMessageWithValidUser(): void
    {
        $this->mockLogger('User username has logged in.');
        $this->mockInteractiveLoginEvent('username');

        $listener = new InteractiveLoginListener($this->logger, $this->framework);
        $listener->onInteractiveLogin($this->interactiveLoginEvent);
    }

    /**
     * Tests the execution of the postLogin hook.
     *
     * @group legacy
     *
     * @expectedDeprecation Using InteractiveLoginListener::triggerLegacyPostLoginHook has been deprecated %s.
     */
    public function testExecutesThePostLoginHook(): void
    {
        $this->framework
            ->expects($this->once())
            ->method('createInstance')
            ->willReturn($this)
        ;

        $GLOBALS['TL_HOOKS'] = [
            'postLogin' => [[\get_class($this), 'executePostLoginHookCallback']],
        ];

        $this->mockLogger('User username has logged in.');
        $this->mockInteractiveLoginEvent('username');

        $listener = new InteractiveLoginListener($this->logger, $this->framework);
        $listener->onInteractiveLogin($this->interactiveLoginEvent);
    }

    /**
     * postLogin Hook stub.
     *
     * @param User $user
     */
    public static function executePostLoginHookCallback(User $user): void
    {
        self::assertInstanceOf('Contao\User', $user);
    }

    /**
     * Mocks the logger service with an optional message.
     *
     * @param string|null $message
     */
    private function mockLogger(string $message = null): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        if (null === $message) {
            $this->logger
                ->expects($this->never())
                ->method('info')
            ;
        }

        if (null !== $message) {
            $context = [
                'contao' => new ContaoContext(
                    'Contao\CoreBundle\EventListener\InteractiveLoginListener::onInteractiveLogin',
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
     * Mocks the Token class with an optional username.
     *
     * @param string|null $expectedUsername
     */
    private function mockToken(string $expectedUsername = null): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        if (null !== $expectedUsername) {
            $user = $this->mockUser($expectedUsername);

            $this->token
                ->expects($this->once())
                ->method('getUser')
                ->willReturn($user)
            ;
        }
    }

    /**
     * Mocks the User with an optional username.
     *
     * @param string|null $expectedUsername
     *
     * @return User
     */
    private function mockUser(string $expectedUsername = null): User
    {
        $user = null;

        if (null !== $expectedUsername) {
            $user = $this
                ->getMockBuilder(User::class)
                ->disableOriginalConstructor()
                ->setMethods(['getUsername'])
                ->getMock()
            ;

            $user->username = $expectedUsername;

            $user
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($expectedUsername)
            ;
        }

        return $user;
    }

    /**
     * Mocks the InteractiveLoginEvent with an optional target username.
     *
     * @param string|null $expectedUsername
     */
    private function mockInteractiveLoginEvent(string $expectedUsername = null): void
    {
        $request = new Request();
        $this->mockToken($expectedUsername);

        $this->interactiveLoginEvent = new InteractiveLoginEvent($request, $this->token);
    }
}
