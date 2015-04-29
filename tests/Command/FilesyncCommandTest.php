<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Command;

use Contao\CoreBundle\Command\FilesyncCommand;
use Contao\CoreBundle\Test\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Tests the FilesyncCommand class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FilesyncCommandTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $command = new FilesyncCommand('contao:filesync', $this->mockDbafs());

        $this->assertInstanceOf('Contao\\CoreBundle\\Command\\FilesyncCommand', $command);
    }

    /**
     * Tests the output.
     */
    public function testOutput()
    {
        $command = new FilesyncCommand('contao:filesync', $this->mockDbafs());
        $tester  = new CommandTester($command);

        $code = $tester->execute([]);

        $this->assertEquals(0, $code);
        $this->assertContains('Synchronization complete (see sync.log).', $tester->getDisplay());
    }

    /**
     * Tests the lock.
     */
    public function testLock()
    {
        $lock = new LockHandler('contao:filesync', $this->mockDbafs());
        $lock->lock();

        $command = new FilesyncCommand('contao:filesync', $this->mockDbafs());
        $tester  = new CommandTester($command);

        $code = $tester->execute([]);

        $this->assertEquals(1, $code);
        $this->assertContains('The command is already running in another process.', $tester->getDisplay());

        $lock->release();
    }

    private function mockDbafs()
    {
        $dbafs = $this->getMock('Contao\\CoreBundle\\Adapter\\DbafsAdapter');
        $dbafs->expects($this->any())->method('syncFiles')->willReturn('sync.log');

        return $dbafs;
    }
}
