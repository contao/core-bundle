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

use Contao\CoreBundle\Exception\UserNotFoundException;
use Contao\CoreBundle\Security\User\SwitchUserButtonGenerator;
use Contao\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SwitchUserButtonGeneratorTest extends TestCase
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Statement
     */
    private $statement;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var UserInterface
     */
    private $tokenUser;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var array
     */
    private $row;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $label;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->row = ['id' => 1];
        $this->title = 'Switch to user ID 2';
        $this->label = 'Switch user';
        $this->router = $this->createMock(RouterInterface::class);

        $this->mockAuthorizationChecker();
        $this->mockTokenStorage();
        $this->mockConnection();
    }

    public function testCanBeInstantiated(): void
    {
        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->tokenStorage
        );

        $this->assertInstanceOf('Contao\CoreBundle\Security\User\SwitchUserButtonGenerator', $switchUserButtonGenerator);
    }

    public function testReturnsAnEmptyStringIfTheUserIsNotAllowedToSwitch(): void
    {
        $this->mockAuthorizationChecker(false);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->tokenStorage
        );

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton($this->row, '', $this->label, $this->title, ''));
    }

    public function testFailsIfTheUserIdIsInvalid(): void
    {
        $this->mockAuthorizationChecker(true);
        $this->mockStatement(0);
        $this->mockConnection($this->statement);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->tokenStorage
        );

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Invalid user ID 1');

        $switchUserButtonGenerator->generateSwitchUserButton($this->row, '', $this->label, $this->title, '');
    }

    public function testReturnsAnEmptyStringIfUserAndTokenDoNotMatch(): void
    {
        $this->mockAuthorizationChecker(true);
        $this->mockTokenStorage('foobar');
        $this->mockUser('foobar');
        $this->mockStatement(1, true, $this->user);
        $this->mockConnection($this->statement);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->tokenStorage
        );

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton($this->row, '', $this->label, $this->title, ''));
    }

    public function testReturnsTheSwitchUserButton(): void
    {
        $this->mockAuthorizationChecker(true);
        $this->mockTokenStorage('foobar');
        $this->mockUser('barfoo');
        $this->mockStatement(1, true, $this->user);
        $this->mockConnection($this->statement);

        $url = sprintf('/contao?_switch_user=%s', $this->user->username);
        $html = sprintf('<a href="%s" title="%s"></a>', $url, $this->title);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('contao_backend')
            ->willReturn($url)
        ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->tokenStorage
        );

        $this->assertSame($html, $switchUserButtonGenerator->generateSwitchUserButton($this->row, '', $this->label, $this->title, ''));
    }

    /**
     * Mocks the Statement object.
     *
     * @param null $expectedRowCount
     * @param bool $fetch
     * @param null $fetchReturn
     */
    private function mockStatement($expectedRowCount = null, $fetch = false, $fetchReturn = null): void
    {
        $this->statement = $this->createMock(Statement::class);

        if (null !== $expectedRowCount) {
            $this->statement
                ->expects($this->once())
                ->method('bindValue')
                ->with('id', 1)
            ;

            $this->statement
                ->expects($this->once())
                ->method('execute')
            ;

            $this->statement
                ->expects($this->once())
                ->method('rowCount')
                ->willReturn($expectedRowCount)
            ;
        }

        if (true === $fetch) {
            $this->statement
                ->expects($this->once())
                ->method('fetch')
                ->with(\PDO::FETCH_OBJ)
                ->willReturn($fetchReturn)
            ;
        }
    }

    /**
     * Mocks the TokenStorage service.
     *
     * @param string|null $expectedUsername
     */
    private function mockTokenStorage(string $expectedUsername = null): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->tokenUser = $this->createMock(UserInterface::class);

        if (null !== $expectedUsername) {
            $this->tokenUser
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($expectedUsername)
            ;

            $this->token
                ->expects($this->once())
                ->method('getUser')
                ->willReturn($this->tokenUser)
            ;

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
     * @param string|null $expectedUsername
     */
    private function mockUser(string $expectedUsername = null): void
    {
        $this->user = $this
            ->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        if (null !== $expectedUsername) {
            $this->user->username = $expectedUsername;
        }
    }

    /**
     * Mocks the database connection object.
     *
     * @param Statement|null $statement
     */
    private function mockConnection(Statement $statement = null): void
    {
        $this->connection = $this->createMock(Connection::class);

        if (null !== $statement) {
            $this->connection
                ->expects($this->once())
                ->method('prepare')
                ->with('SELECT id, username FROM tl_user WHERE id = :id')
                ->willReturn($statement)
            ;
        }
    }

    /**
     * Mocks the AuthorizationChecker.
     *
     * @param bool|null $isRoleSwitchGranted
     */
    private function mockAuthorizationChecker(bool $isRoleSwitchGranted = null): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        if (null !== $isRoleSwitchGranted) {
            $this->authorizationChecker
                ->expects($this->once())
                ->method('isGranted')
                ->with('ROLE_ALLOWED_TO_SWITCH')
                ->willReturn($isRoleSwitchGranted)
            ;
        }
    }
}
