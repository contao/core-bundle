<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\EventListener\TokenLifetimeListener;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TokenLifetimeListenerTest extends TestCase
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ScopeMatcher
     */
    protected $scopeMatcher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $tokenLifetime;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var GetResponseEvent
     */
    protected $getResponseEvent;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->tokenLifetime = 3600;
        $this->mockLogger();
        $this->getTokenStorageMock();
        $this->mockGetResponseEvent();
        $this->scopeMatcher = $this->mockScopeMatcher();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\TokenLifetimeListener', $listener);
    }

    /**
     * Tests if the listener immediatly returns if its not a master request.
     */
    public function testImmediateReturnWhenNotMasterRequest(): void
    {
        $this->mockGetResponseEvent(HttpKernelInterface::SUB_REQUEST);

        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken')
        ;

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
    }

    /**
     * Tests if the listener immediatly returns if the token is not a UsernamePasswordToken.
     */
    public function testImmediateReturnWhenNoUsernamePasswordTokenIsGiven(): void
    {
        $this->mockGetResponseEvent();
        $this->getTokenStorageMock(true, TokenInterface::class);

        $this->token
            ->expects($this->never())
            ->method('getUser')
        ;

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
    }

    /**
     * Tests if the listener immediatly returns if the user is not a Contao user.
     */
    public function testImmediateReturnWhenNoContaoUserIsGiven(): void
    {
        $this->mockGetResponseEvent();
        $this->getTokenStorageMock(true);

        $this->token
            ->expects($this->never())
            ->method('hasAttribute')
        ;

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
    }

    /**
     * Tests if the listener sets the lifetime if lifetime is not set yet.
     */
    public function testIfLifetimeGetsSetWhenNoLifetimeIsSet(): void
    {
        $this->tokenLifetime = null;
        $this->mockGetResponseEvent();
        $this->getTokenStorageMock(true);

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, 3600, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
    }

    /**
     * Tests if the listener updates the lifetime if lifetime is still valid.
     */
    public function testIfLifetimeGetsUpdatedIfLifetimeIsStillValid(): void
    {
        $this->mockGetResponseEvent();
        $this->getTokenStorageMock(true);

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->mockUser())
        ;

        $this->token
            ->expects($this->once())
            ->method('hasAttribute')
            ->with('lifetime')
            ->willReturn(true)
        ;

        $this->token
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn(time() + 5000)
        ;

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
    }

    /**
     * Tests if the listener revokes the token after inactivity.
     */
    public function testIfTokenGetsRevokedAfterInacitivty(): void
    {
        $this->mockGetResponseEvent();
        $this->getTokenStorageMock(true);
        $this->mockLogger('User foobar has been logged out automatically due to inactivity.');

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->mockUser('foobar'))
        ;

        $this->token
            ->expects($this->once())
            ->method('hasAttribute')
            ->with('lifetime')
            ->willReturn(true)
        ;

        $this->token
            ->expects($this->once())
            ->method('getAttribute')
            ->with('lifetime')
            ->willReturn(0)
        ;

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with(null)
        ;

        $listener = new TokenLifetimeListener($this->tokenStorage, $this->scopeMatcher, $this->tokenLifetime, $this->logger);
        $listener->onKernelRequest($this->getResponseEvent);
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
                    'Contao\CoreBundle\EventListener\TokenLifetimeListener::onKernelRequest',
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
     * Mocks the TokenStorage service with an optional token lifetime.
     *
     * @param bool   $withToken
     * @param string $tokenClass
     */
    private function getTokenStorageMock(bool $withToken = false, string $tokenClass = UsernamePasswordToken::class): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock($tokenClass);

        if (true === $withToken) {
            $this->tokenStorage
                ->expects($this->once())
                ->method('getToken')
                ->willReturn($this->token)
            ;
        }
    }

    /**
     * Mocks the User.
     *
     * @param string|null $username
     *
     * @return User
     */
    private function mockUser(string $username = null): User
    {
        $user = $this
            ->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsername'])
            ->getMock()
        ;

        if (null !== $username) {
            $user
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn('foobar')
            ;
        }

        return $user;
    }

    /**
     * Mocks the GetResponseEvent.
     *
     * @param int $requestType
     */
    private function mockGetResponseEvent(int $requestType = KernelInterface::MASTER_REQUEST): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $request = new Request();
        $request->attributes->set('_scope', 'backend');

        $this->getResponseEvent = new GetResponseEvent($kernel, $request, $requestType);
    }
}
