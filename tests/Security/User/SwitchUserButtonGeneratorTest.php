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
use Contao\CoreBundle\Tests\TestCase;
use Contao\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
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

    public function setUp()
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $this->engine = $this->createMock(EngineInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->statement = $this->createMock(Statement::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->tokenUser = $this->createMock(UserInterface::class);

        $this->user = $this
            ->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->user->username = 'foo';
    }

    private function setUpStatement($expectedRowCount)
    {
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

    private function setUpToken($expectedUsername)
    {
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
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false)
        ;

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
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $this->setUpStatement(0);

        $this->connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement)
        ;

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
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $this->setUpToken('foo');
        $this->setUpStatement(1);

        $this->connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement)
        ;

        $this->statement
            ->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_OBJ)
            ->willReturn($this->user)
         ;

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
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $this->setUpToken('bar');
        $this->setUpStatement(1);

        $url = sprintf('/contao?_switch_user=%s', $this->user->username);
        $title = 'Switch to user ID 2';
        $label = 'Switch user';
        $image = '<img src="system/themes/flexible/icons/su.svg" width="16" height="16" alt="Switch user">';
        $html = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $image);

        $this->connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement)
        ;

        $this->statement
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($this->user)
        ;

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->willReturn($url)
        ;

        $this->engine
            ->expects($this->once())
            ->method('render')
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
}
