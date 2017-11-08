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
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $connection = $this->createMock(Connection::class);
        $engine = $this->createMock(EngineInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $switchUserButtonGenerator = new SwitchUserButtonGenerator($authorizationChecker, $router, $connection, $engine, $tokenStorage);

        $this->assertInstanceOf('Contao\CoreBundle\Security\User\SwitchUserButtonGenerator', $switchUserButtonGenerator);
    }

    /**
     * Tests empty string response if user has no role allowed to switch.
     */
    public function testReturnsEmptyStringIfUserHasNoRoleAllowedToSwitch(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $connection = $this->createMock(Connection::class);
        $engine = $this->createMock(EngineInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(false)
        ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator($authorizationChecker, $router, $connection, $engine, $tokenStorage);

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton([], '', '', '', ''));
    }

    /**
     * Tests UserNotFoundException thrown when user ID is invalid.
     */
    public function testThrowsUserNotFoundExceptionWhenUserIdIsInvalid(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $connection = $this->createMock(Connection::class);
        $engine = $this->createMock(EngineInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $statement = $this->createMock(Statement::class);

        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $statement
            ->expects($this->once())
            ->method('bindValue')
        ;

        $statement
            ->expects($this->once())
            ->method('execute')
        ;

        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn(0)
        ;

        $connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement)
        ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator($authorizationChecker, $router, $connection, $engine, $tokenStorage);

        $this->expectException('Contao\CoreBundle\Exception\UserNotFoundException');
        $switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', '', '', '');
    }

    /**
     * Tests empty string response when user and token not match.
     */
    public function testReturnsEmptyStringWhenUserAndTokenNotMatch(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $connection = $this->createMock(Connection::class);
        $engine = $this->createMock(EngineInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $statement = $this->createMock(Statement::class);
        $token = $this->createMock(TokenInterface::class);
        $tokenUser = $this->createMock(UserInterface::class);

        $user = $this
            ->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $tokenUser
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('foo')
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($tokenUser)
        ;

        $user->username = 'foo';

        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $statement
            ->expects($this->once())
            ->method('bindValue')
        ;

        $statement
            ->expects($this->once())
            ->method('execute')
        ;

        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn(1)
        ;

        $connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement)
        ;

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $statement
            ->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_OBJ)
            ->willReturn($user)
         ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator($authorizationChecker, $router, $connection, $engine, $tokenStorage);

        $this->assertEmpty($switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', '', '', ''));
    }

    /**
     * Tests button html response.
     */
    public function testReturnsButtonHtml(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $connection = $this->createMock(Connection::class);
        $engine = $this->createMock(EngineInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $statement = $this->createMock(Statement::class);
        $token = $this->createMock(TokenInterface::class);
        $tokenUser = $this->createMock(UserInterface::class);

        $user = $this
            ->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $tokenUser
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('foo')
        ;

        $token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($tokenUser)
        ;

        $user->username = 'bar';
        $url = sprintf('/contao?_switch_user=%s', $user->username);
        $title = 'Switch to user ID 2';
        $label = 'Switch user';
        $image = '<img src="system/themes/flexible/icons/su.svg" width="16" height="16" alt="Switch user">';
        $html = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $image);

        $authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->willReturn(true)
        ;

        $statement
            ->expects($this->once())
            ->method('bindValue')
        ;

        $statement
            ->expects($this->once())
            ->method('execute')
        ;

        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn(1)
        ;

        $connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement)
        ;

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token)
        ;

        $statement
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($user)
        ;

        $router
            ->expects($this->once())
            ->method('generate')
            ->willReturn($url)
        ;

        $engine
            ->expects($this->once())
            ->method('render')
            ->willReturn($html)
        ;

        $switchUserButtonGenerator = new SwitchUserButtonGenerator($authorizationChecker, $router, $connection, $engine, $tokenStorage);

        $this->assertSame($html, $switchUserButtonGenerator->generateSwitchUserButton(['id' => 1], '', $label, $title, ''));
    }
}
