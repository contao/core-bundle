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

use Contao\BackendUser;
use Contao\CoreBundle\EventListener\SwitchUserListener;
use Contao\CoreBundle\Monolog\ContaoContext;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

/**
 * Tests the SwitchUserListener class.
 */
class SwitchUserListenerTest extends TestCase
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var SwitchUserEvent
     */
    protected $switchUserEvent;

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $this->mockLogger();
        $this->mockTokenStorage();

        $listener = new SwitchUserListener($this->logger, $this->tokenStorage);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\SwitchUserListener', $listener);
    }

    /**
     * Tests the logging on the SwitchUserEvent.
     */
    public function testOnSwitchUserEvent(): void
    {
        $this->mockLogger('User user1 has switched to user user2.');
        $this->mockTokenStorage('user1');
        $this->mockSwitchUserEvent('user2');

        $listener = new SwitchUserListener($this->logger, $this->tokenStorage);
        $listener->onSwitchUser($this->switchUserEvent);
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
                    'Contao\CoreBundle\EventListener\SwitchUserListener::onSwitchUser',
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
     * Mocks the TokenStorage service with an optional username.
     *
     * @param null|string $expectedUsername
     */
    private function mockTokenStorage(string $expectedUsername = null): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock(TokenInterface::class);

        if (null !== $expectedUsername) {
            $user = $this->mockBackendUser($expectedUsername);

            $this->token
                ->expects($this->once())
                ->method('getUser')
                ->willReturn($user)
            ;

            $this->tokenStorage
                ->expects($this->once())
                ->method('getToken')
                ->willReturn($this->token)
            ;
        }
    }

    /**
     * Mocks the User with an optional username.
     *
     * @param null|string $expectedUsername
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockBackendUser(string $expectedUsername = null): \PHPUnit_Framework_MockObject_MockObject
    {
        $user = $this
            ->getMockBuilder(BackendUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsername'])
            ->getMock()
        ;

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

    /**
     * Mocks the SwitchUserEvent with an optional target username.
     *
     * @param string|null $expectedUsername
     */
    private function mockSwitchUserEvent(string $expectedUsername = null): void
    {
        $request = new Request();

        /** @var UserInterface $targetUser */
        $targetUser = $this->mockBackendUser($expectedUsername);

        $this->switchUserEvent = new SwitchUserEvent($request, $targetUser);
    }
}
