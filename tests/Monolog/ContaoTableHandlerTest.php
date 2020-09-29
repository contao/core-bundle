<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Monolog;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Monolog\ContaoTableHandler;
use Contao\CoreBundle\Tests\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the ContaoTableHandler class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoTableHandlerTest extends TestCase
{
    /**
     * Tests setting and retrieving the DBAL service name.
     */
    public function testSupportsReadingAndWritingTheDbalServiceName()
    {
        $handler = new ContaoTableHandler();

        $this->assertSame('doctrine.dbal.default_connection', $handler->getDbalServiceName());

        $handler->setDbalServiceName('foobar');

        $this->assertSame('foobar', $handler->getDbalServiceName());
    }

    /**
     * Tests the handle() method.
     *
     * @group legacy
     *
     * @expectedDeprecation Using the addLogEntry hook has been deprecated %s.
     */
    public function testHandlesContaoRecords()
    {
        $record = [
            'level' => Logger::DEBUG,
            'extra' => ['contao' => new ContaoContext('foobar')],
            'context' => [],
            'datetime' => new \DateTime(),
            'message' => 'foobar',
        ];

        $statement = $this->createMock(Statement::class);

        $statement
            ->expects($this->once())
            ->method('execute')
        ;

        $connection = $this->createMock(Connection::class);

        $connection
            ->method('prepare')
            ->willReturn($statement)
        ;

        $container = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get'])
            ->getMock()
        ;

        $container
            ->method('has')
            ->willReturn(true)
        ;

        $container
            ->method('get')
            ->willReturnCallback(function ($key) use ($connection) {
                switch ($key) {
                    case 'contao.framework':
                        $system = $this
                            ->getMockBuilder(Adapter::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['importStatic', 'addLogEntry'])
                            ->getMock()
                        ;

                        $system
                            ->method('importStatic')
                            ->willReturn($this)
                        ;

                        $framework = $this->createMock(ContaoFrameworkInterface::class);

                        $framework
                            ->method('isInitialized')
                            ->willReturn(true)
                        ;

                        $framework
                            ->method('getAdapter')
                            ->willReturn($system)
                        ;

                        return $framework;

                    case 'doctrine.dbal.default_connection':
                        return $connection;
                }

                return null;
            })
        ;

        $GLOBALS['TL_HOOKS']['addLogEntry'][] = [static::class, 'addLogEntry'];

        $handler = new ContaoTableHandler();
        $handler->setContainer($container);

        $this->assertFalse($handler->handle($record));
    }

    /**
     * Dummy method to test the addLogEntry hook.
     */
    public function addLogEntry()
    {
        // ignore
    }

    /**
     * Tests that the handler does nothing if the log level does not match.
     */
    public function testDoesNotHandleARecordIfTheLogLevelDoesNotMatch()
    {
        $handler = new ContaoTableHandler();
        $handler->setLevel(Logger::INFO);

        $this->assertFalse($handler->handle(['level' => Logger::DEBUG]));
    }

    /**
     * Tests that the handle() method returns false if there is no Contao context.
     */
    public function testDoesNotHandleARecordWithoutContaoContext()
    {
        $record = [
            'level' => Logger::DEBUG,
            'extra' => ['contao' => null],
            'context' => [],
        ];

        $handler = new ContaoTableHandler();

        $this->assertFalse($handler->handle($record));
    }

    /**
     * Tests the handle() method.
     */
    public function testDoesNotHandleTheRecordIfThereIsNoContainer()
    {
        $record = [
            'level' => Logger::DEBUG,
            'extra' => ['contao' => new ContaoContext('foobar')],
            'context' => [],
            'datetime' => new \DateTime(),
            'message' => 'foobar',
        ];

        $handler = new ContaoTableHandler();

        $this->assertFalse($handler->handle($record));
    }
}
