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
use Symfony\Component\Templating\EngineInterface;

/**
 * Tests the SwitchUserButtonGenerator class.
 */
class SwitchUserButtonGeneratorTest extends TestCase
{
    protected $authorizationChecker;
    protected $router;
    protected $connection;
    protected $engine;
    protected $tokenStorage;
    protected $statement;
    protected $token;
    protected $tokenUser;
    protected $user;

    public function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->engine = $this->createMock(EngineInterface::class);

        $this->mockAuthorizationChecker();
        $this->mockTokenStorage();
        $this->mockConnection();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->engine,
            $this->tokenStorage
        );

        $this->assertInstanceOf(SwitchUserButtonGenerator::class, $switchUserButtonGenerator);
    }

    /**
     * Tests empty string response if user has no role allowed to switch.
     */
    public function testReturnsEmptyStringIfUserHasNoRoleAllowedToSwitch(): void
    {
        $this->mockAuthorizationChecker(false);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->engine,
            $this->tokenStorage
        );

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton([], '', '', '', ''));
    }

    /**
     * Tests UserNotFoundException thrown when user ID is invalid.
     */
    public function testThrowsUserNotFoundExceptionWhenUserIdIsInvalid(): void
    {
        $this->mockAuthorizationChecker(true);
        $this->mockStatement(0);
        $this->mockConnection($this->statement);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->engine,
            $this->tokenStorage
        );

        $this->expectException('Contao\CoreBundle\Exception\UserNotFoundException');
        $switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', '', '', '');
    }

    /**
     * Tests empty string response when user and token not match.
     */
    public function testReturnsEmptyStringWhenUserAndTokenNotMatch(): void
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
            $this->engine,
            $this->tokenStorage
        );

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', '', '', ''));
    }

    /**
     * Tests button html response.
     */
    public function testReturnsButtonHtml(): void
    {
        $this->mockAuthorizationChecker(true);
        $this->mockTokenStorage('foobar');
        $this->mockUser('barfoo');
        $this->mockStatement(1, true, $this->user);
        $this->mockConnection($this->statement);

        $url = sprintf('/contao?_switch_user=%s', $this->user->username);
        $title = 'Switch to user ID 2';
        $label = 'Switch user';
        $image = '<img src="system/themes/flexible/icons/su.svg" width="16" height="16" alt="Switch user">';
        $html = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $image);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('contao_backend')
            ->willReturn($url)
        ;

        $this->engine
            ->expects($this->once())
            ->method('render')
            ->with('@ContaoCore/Backend/switch_user.html.twig')
            ->willReturn($html)
        ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator(
            $this->authorizationChecker,
            $this->router,
            $this->connection,
            $this->engine,
            $this->tokenStorage
        );

        $this->assertSame($html, $switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', $label, $title, ''));
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
     * @param null $expectedUsername
     */
    private function mockTokenStorage($expectedUsername = null): void
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
     * @param null $expectedUsername
     */
    private function mockUser($expectedUsername = null): void
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
