<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Security\LogoutHandler;
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Tests the LogoutHandler class.
 */
class LogoutHandlerTest extends TestCase
{
    protected $logger;
    protected $request;
    protected $response;
    protected $token;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        unset($GLOBALS['TL_HOOKS']);

        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->mockLogger();
        $handler = new LogoutHandler($this->logger);

        $this->assertInstanceOf('Contao\CoreBundle\Security\LogoutHandler', $handler);
    }

    /**
     * Tests if the handler immediatly returns if no user is available.
     */
    public function testImmediateReturnIfNoUserIsGiven(): void
    {
        $this->mockLogger();
        $this->mockToken(false);

        $handler = new LogoutHandler($this->logger);

        $this->assertEmpty($handler->logout($this->request, $this->response, $this->token));
    }

    /**
     * Tests the logger message with a valid user given.
     */
    public function testLoggerMessageWithValidUser(): void
    {
        $this->mockLogger('User username has logged out.');
        $this->mockToken(true);

        $handler = new LogoutHandler($this->logger);

        $this->assertEmpty($handler->logout($this->request, $this->response, $this->token));
    }

    /**
     * Tests the execution of the postLogout hook.
     */
    public function testExecutesThePostLogoutHook(): void
    {
        $GLOBALS['TL_HOOKS'] = [
            'postLogout' => [[\get_class($this), 'executePostLogoutHookCallback']],
        ];

        $this->mockLogger('User username has logged out.');
        $this->mockToken(true);

        $handler = new LogoutHandler($this->logger);

        $this->assertEmpty($handler->logout($this->request, $this->response, $this->token));
    }

    /**
     * postLogout hook stub.
     *
     * @param User $user
     */
    public static function executePostLogoutHookCallback(User $user): void
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

        if (null !== $message) {
            $context = [
                'contao' => new ContaoContext(
                    'Contao\CoreBundle\Security\LogoutHandler::logout',
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
     * Mocks a Token with an optional valid user.
     *
     * @param bool $validUser
     */
    private function mockToken(bool $validUser = false): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        $user = null;

        if ($validUser) {
            $user = $this->mockUser('username');
        }

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;
    }

    /**
     * Mocks the User with an optional username.
     *
     * @param null|string $expectedUsername
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockUser(string $expectedUsername = null): \PHPUnit_Framework_MockObject_MockObject
    {
        $user = $this->createPartialMock(User::class, ['getUsername']);

        if (null !== $expectedUsername) {
            $user->username = $expectedUsername;
            $user
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($expectedUsername)
            ;
        }

        return $user;
    }
}
