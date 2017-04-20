<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests;

use Contao\CoreBundle\Cors\WebsiteRootsConfigProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the WebsiteRootsConfigProvider class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class WebsiteRootsConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $configProvider = new WebsiteRootsConfigProvider($connection);

        $this->assertInstanceOf('Contao\CoreBundle\Cors\WebsiteRootsConfigProvider', $configProvider);
    }

    /**
     * Tests that a configuration is provided if the host matches.
     */
    public function testConfigProvidedIfHostMatches()
    {
        $request = Request::create('https://foobar.com');
        $request->headers->set('Origin', 'http://origin.com');
        $statement = $this->getMock(Statement::class);

        $statement
            ->expects($this->at(0))
            ->method('bindValue')
            ->with('dns', 'origin.com')
        ;

        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn(1)
        ;

        $connection = $this->getConnection($statement);
        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertEquals(
            [
                'allow_origin' => true,
                'allow_methods' => ['HEAD', 'GET'],
                'allow_headers' => ['x-requested-with'],
            ],
            $result
        );
    }

    /**
     * Tests that no configuration is provided if the host does not match.
     */
    public function testNoConfigProvidedIfHostDoesNotMatch()
    {
        $request = Request::create('https://foobar.com');
        $request->headers->set('Origin', 'https://origin.com');
        $statement = $this->getMock(Statement::class);

        $statement
            ->expects($this->at(0))
            ->method('bindValue')
            ->with('dns', 'origin.com')
        ;

        $statement
            ->expects($this->once())
            ->method('rowCount')
            ->willReturn(0)
        ;

        $connection = $this->getConnection($statement);
        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertCount(0, $result);
    }

    /**
     * Tests that no configuration is provided if there is no origin header.
     */
    public function testNoConfigProvidedIfNoOrigin()
    {
        $request = Request::create('http://foobar.com');
        $request->headers->remove('Origin');

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $connection
            ->expects($this->never())
            ->method('prepare')
        ;

        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertCount(0, $result);
    }

    /**
     * Tests that no configuration is provided if the origin is empty.
     */
    public function testNoConfigProvidedIfOriginEmpty()
    {
        $request = Request::create('https://foobar.com');
        $request->headers->set('Origin', '');

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $connection
            ->expects($this->never())
            ->method('prepare')
        ;

        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertCount(0, $result);
    }

    /**
     * Tests that no configuration is provided if the database is not connected.
     */
    public function testNoConfigProvidedIfDatabaseNotConnected()
    {
        $request = Request::create('https://foobar.com');
        $request->headers->set('Origin', 'https://origin.com');

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $connection
            ->expects($this->any())
            ->method('isConnected')
            ->willReturn(false)
        ;

        $connection
            ->expects($this->never())
            ->method('prepare')
        ;

        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertCount(0, $result);
    }

    /**
     * Tests that no configuration is provided if the table does not exist.
     */
    public function testNoConfigProvidedIfTableDoesNotExist()
    {
        $request = Request::create('https://foobar.com');
        $request->headers->set('Origin', 'https://origin.com');

        $schemaManager = $this
            ->getMockBuilder(MySqlSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $schemaManager
            ->expects($this->once())
            ->method('tablesExist')
            ->willReturn(false)
        ;

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $connection
            ->expects($this->any())
            ->method('isConnected')
            ->willReturn(true)
        ;

        $connection
            ->expects($this->any())
            ->method('getSchemaManager')
            ->willReturn($schemaManager)
        ;

        $connection
            ->expects($this->never())
            ->method('prepare')
        ;

        $configProvider = new WebsiteRootsConfigProvider($connection);
        $result = $configProvider->getOptions($request);

        $this->assertCount(0, $result);
    }

    /**
     * Mocks a database connection object.
     *
     * @param string $statement
     *
     * @return Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConnection($statement)
    {
        $schemaManager = $this
            ->getMockBuilder(MySqlSchemaManager::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $schemaManager
            ->expects($this->once())
            ->method('tablesExist')
            ->willReturn(true)
        ;

        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $connection
            ->expects($this->once())
            ->method('isConnected')
            ->willReturn(true)
        ;

        $connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($statement)
        ;

        $connection
            ->expects($this->once())
            ->method('getSchemaManager')
            ->willReturn($schemaManager)
        ;

        return $connection;
    }
}
